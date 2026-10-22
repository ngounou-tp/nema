<?php

namespace App\Services\AI\Exceptions;

/**
 * The provider answered, but not with the JSON object that was asked for.
 */
class InvalidAiJsonException extends AiProviderException
{
    public function __construct(string $provider, string $reason)
    {
        parent::__construct($provider, "Invalid JSON answer: {$reason}", 200);
    }
}
