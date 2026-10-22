<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;

/**
 * The user reached config('nema.ai.daily_limit_per_user') AI calls today.
 */
class AiQuotaExceededException extends RuntimeException
{
    public function __construct(public readonly int $limit)
    {
        parent::__construct("Daily AI limit of {$limit} calls reached.");
    }
}
