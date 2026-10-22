<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;
use Throwable;

/**
 * One provider failed (HTTP error, timeout, unusable answer). The manager moves on to the next one.
 */
class AiProviderException extends RuntimeException
{
    /**
     * @param  int|null  $status  HTTP status, null on timeout or connection failure
     */
    public function __construct(
        public readonly string $provider,
        string $message,
        public readonly ?int $status = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }
}
