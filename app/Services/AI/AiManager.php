<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Exceptions\AiProviderException;
use App\Services\AI\Exceptions\AiQuotaExceededException;
use App\Services\AI\Exceptions\AiUnavailableException;
use App\Services\AI\Exceptions\InvalidAiJsonException;
use Illuminate\Support\Facades\Cache;

/**
 * Entry point of the AI layer: tries the text providers in the order of
 * config('nema.ai.text_providers'), caches identical requests for 24 h,
 * enforces the daily quota and, in demo mode, falls back to prepared answers.
 */
class AiManager
{
    /**
     * @var array<string, class-string<AiClient>>
     */
    public const array PROVIDERS = [
        'groq' => GroqClient::class,
        'openrouter' => OpenRouterClient::class,
        'gemini' => GeminiClient::class,
    ];

    public function __construct(
        private AiQuota $quota,
        private DemoFallbacks $fallbacks,
        private AiCallLogger $logger,
        private GeminiClient $vision,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array{user?: User, cache?: bool, max_tokens?: int, temperature?: float, json?: bool, timeout?: int}  $options
     *
     * @throws AiQuotaExceededException
     * @throws AiUnavailableException when every provider fails (and no demo fallback applies)
     */
    public function chat(array $messages, array $options = []): AiResponse
    {
        $user = $options['user'] ?? null;
        $useCache = $options['cache'] ?? true;
        unset($options['user'], $options['cache']);

        if ($user !== null) {
            $this->quota->ensureAvailable($user);
        }

        $clients = $this->textClients();
        $cacheKey = $this->cacheKey($messages, $options, $clients);

        if ($useCache && ($cached = AiResponse::fromArray(Cache::get($cacheKey))) !== null) {
            return $cached;
        }

        try {
            $response = $this->firstSuccessful($clients, $messages, $options);
        } catch (AiUnavailableException $exception) {
            return $this->fallback($messages) ?? throw $exception;
        }

        if ($user !== null) {
            $this->quota->hit($user);
        }

        if ($useCache) {
            Cache::put($cacheKey, $response->toArray(), (int) config('nema.ai.cache_ttl'));
        }

        return $response;
    }

    /**
     * Photo diagnosis through Gemini (the only vision provider).
     *
     * @return array<string, mixed>
     *
     * @throws AiQuotaExceededException
     * @throws InvalidAiJsonException
     * @throws AiUnavailableException
     */
    public function analyzeImage(string $prompt, string $bytes, string $mime, ?User $user = null): array
    {
        if ($user !== null) {
            $this->quota->ensureAvailable($user);
        }

        try {
            $result = $this->vision->analyzeImage($prompt, $bytes, $mime);
        } catch (InvalidAiJsonException $exception) {
            if ($user !== null) {
                $this->quota->hit($user);
            }

            throw $exception;
        } catch (AiProviderException $exception) {
            throw new AiUnavailableException([$exception]);
        }

        if ($user !== null) {
            $this->quota->hit($user);
        }

        return $result;
    }

    /**
     * Configured text providers, in the configured order; unknown names are ignored.
     *
     * @return list<AiClient>
     */
    public function textClients(): array
    {
        $clients = [];

        foreach (array_unique(array_map('trim', explode(',', (string) config('nema.ai.text_providers')))) as $name) {
            if (! isset(self::PROVIDERS[$name])) {
                continue;
            }

            $client = app(self::PROVIDERS[$name]);

            if ($client->isConfigured()) {
                $clients[] = $client;
            }
        }

        return $clients;
    }

    /**
     * @param  list<AiClient>  $clients
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     *
     * @throws AiUnavailableException
     */
    private function firstSuccessful(array $clients, array $messages, array $options): AiResponse
    {
        $failures = [];

        foreach ($clients as $client) {
            try {
                return $client->chat($messages, $options);
            } catch (AiProviderException $exception) {
                $failures[] = $exception;
            }
        }

        throw new AiUnavailableException($failures);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    private function fallback(array $messages): ?AiResponse
    {
        if (! config('nema.demo_mode')) {
            return null;
        }

        $response = $this->fallbacks->match($messages);

        if ($response !== null) {
            $this->logger->fallback($response->model, app()->getLocale());
        }

        return $response;
    }

    /**
     * Same messages, options, provider models and locale → same key
     * (French and English answers never share an entry).
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @param  list<AiClient>  $clients
     */
    private function cacheKey(array $messages, array $options, array $clients): string
    {
        $models = array_map(fn (AiClient $client): string => $client->name().':'.$client->model(), $clients);

        return 'ai:chat:'.hash('sha256', (string) json_encode([$messages, $options, $models, app()->getLocale()]));
    }
}
