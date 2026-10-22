<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;

/**
 * Every configured provider failed, or none is configured.
 */
class AiUnavailableException extends RuntimeException
{
    /**
     * @param  list<AiProviderException>  $failures
     */
    public function __construct(public readonly array $failures = [])
    {
        parent::__construct($failures === []
            ? 'No AI provider is configured.'
            : 'Every AI provider failed: '.implode(', ', array_map(
                fn (AiProviderException $failure): string => $failure->provider.' ('.($failure->status ?? 'timeout').')',
                $failures,
            )).'.');
    }
}
