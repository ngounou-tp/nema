<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AiProviderException;

/**
 * A text AI provider. Keys and models are read through config() only.
 */
interface AiClient
{
    /**
     * Send a conversation and return the assistant's answer.
     *
     * @param  list<array{role: string, content: string}>  $messages  roles: system, user, assistant
     * @param  array{max_tokens?: int, temperature?: float, json?: bool, timeout?: int}  $options
     *
     * @throws AiProviderException when the provider fails (HTTP error, timeout, empty answer)
     */
    public function chat(array $messages, array $options = []): AiResponse;

    /**
     * Provider identifier used in config('nema.ai.text_providers') and in the logs.
     */
    public function name(): string;

    public function model(): string;

    /**
     * Whether an API key is configured; unconfigured providers are skipped.
     */
    public function isConfigured(): bool;
}
