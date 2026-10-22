<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AiProviderException;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shared HTTP plumbing for the providers: timeouts, one retry, timing, logging,
 * and every failure turned into an AiProviderException.
 */
abstract class HttpAiClient implements AiClient
{
    public function __construct(protected AiCallLogger $logger) {}

    /**
     * Key in config/services.php (groq, openrouter, gemini).
     */
    abstract protected function configKey(): string;

    /**
     * Add the provider's authentication to the request.
     */
    abstract protected function authenticate(PendingRequest $request): PendingRequest;

    public function name(): string
    {
        return $this->configKey();
    }

    public function model(): string
    {
        return (string) config("services.{$this->configKey()}.model");
    }

    public function isConfigured(): bool
    {
        return filled(config("services.{$this->configKey()}.key"));
    }

    protected function apiKey(): string
    {
        return (string) config("services.{$this->configKey()}.key");
    }

    /**
     * POST a JSON payload and extract the answer text.
     *
     * @param  array<string, mixed>  $payload
     * @param  Closure(array<string, mixed>): array{0: string, 1: int|null}  $extract  body → [content, tokens]
     * @return array{content: string, tokens: int|null, durationMs: int}
     *
     * @throws AiProviderException
     */
    protected function post(string $operation, string $url, array $payload, int $timeout, Closure $extract): array
    {
        if (! $this->isConfigured()) {
            throw new AiProviderException($this->name(), 'No API key configured.');
        }

        $startedAt = hrtime(true);

        try {
            $response = $this->request($timeout)->post($url, $payload);

            if ($response->failed()) {
                throw new AiProviderException($this->name(), $this->errorMessage($response), $response->status());
            }

            [$content, $tokens] = $extract($response->json() ?? []);

            if (trim($content) === '') {
                throw new AiProviderException($this->name(), 'Empty answer (finish reason: '.$this->finishReason($response).').', $response->status());
            }
        } catch (ConnectionException $exception) {
            throw $this->logFailure($operation, $startedAt, new AiProviderException(
                $this->name(),
                $this->redact('Connection failed or timed out: '.$exception->getMessage()),
                previous: $exception,
            ));
        } catch (AiProviderException $exception) {
            throw $this->logFailure($operation, $startedAt, $exception);
        }

        $durationMs = $this->elapsedMs($startedAt);

        $this->logger->success($this->name(), $this->model(), $operation, $tokens, $durationMs);

        return ['content' => $content, 'tokens' => $tokens, 'durationMs' => $durationMs];
    }

    private function logFailure(string $operation, int $startedAt, AiProviderException $failure): AiProviderException
    {
        $this->logger->failure($this->name(), $this->model(), $operation, $this->elapsedMs($startedAt), $failure->getMessage(), $failure->status);

        return $failure;
    }

    private function elapsedMs(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }

    /**
     * Retries once on timeouts and server errors; 429 goes straight back to the manager,
     * which moves on to the next provider instead of waiting.
     */
    private function request(int $timeout): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout($timeout)
            ->retry(
                1 + (int) config('nema.ai.retries'),
                500,
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
                throw: false,
            );

        return $this->authenticate($request);
    }

    private function errorMessage(Response $response): string
    {
        $message = $response->json('error.message');

        return $this->redact('HTTP '.$response->status().(is_string($message) ? ': '.$message : ''));
    }

    protected function finishReason(Response $response): string
    {
        return (string) ($response->json('choices.0.finish_reason') ?? 'unknown');
    }

    /**
     * Error texts end up in the logs: strip the key in case a provider echoes it back.
     */
    private function redact(string $message): string
    {
        $key = $this->apiKey();

        return Str::limit($key === '' ? $message : str_replace($key, '[redacted]', $message), 300);
    }
}
