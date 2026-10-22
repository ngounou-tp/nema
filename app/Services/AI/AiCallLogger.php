<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * Journal of every AI call. Only metadata is written: never a key, a header or a prompt.
 */
class AiCallLogger
{
    public function success(string $provider, string $model, string $operation, ?int $tokens, int $durationMs): void
    {
        $this->channel()->info('ai.call', [
            'provider' => $provider,
            'model' => $model,
            'operation' => $operation,
            'tokens' => $tokens,
            'duration_ms' => $durationMs,
            'error' => null,
        ]);
    }

    public function failure(string $provider, string $model, string $operation, int $durationMs, string $error, ?int $status = null): void
    {
        $this->channel()->warning('ai.call', [
            'provider' => $provider,
            'model' => $model,
            'operation' => $operation,
            'tokens' => null,
            'duration_ms' => $durationMs,
            'status' => $status,
            'error' => $error,
        ]);
    }

    public function fallback(string $fallbackId, string $locale): void
    {
        $this->channel()->notice('ai.fallback', [
            'fallback' => $fallbackId,
            'locale' => $locale,
        ]);
    }

    private function channel(): LoggerInterface
    {
        return Log::channel(config('nema.ai.log_channel'));
    }
}
