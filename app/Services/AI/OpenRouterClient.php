<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;

/**
 * OpenRouter: backup text provider, skipped when OPENROUTER_API_KEY is empty.
 */
class OpenRouterClient extends OpenAiCompatibleClient
{
    protected function configKey(): string
    {
        return 'openrouter';
    }

    protected function endpoint(): string
    {
        return 'https://openrouter.ai/api/v1/chat/completions';
    }

    /**
     * OpenRouter's optional attribution headers.
     */
    protected function authenticate(PendingRequest $request): PendingRequest
    {
        return parent::authenticate($request)->withHeaders([
            'HTTP-Referer' => (string) config('app.url'),
            'X-Title' => (string) config('app.name'),
        ]);
    }
}
