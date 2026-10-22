<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Exceptions\AiQuotaExceededException;
use Illuminate\Support\Facades\Cache;

/**
 * Daily cap of AI calls per user (config('nema.ai.daily_limit_per_user')), reset at midnight.
 * The per-minute limit is the "ai" RateLimiter, applied on the routes.
 */
class AiQuota
{
    public function limit(): int
    {
        return (int) config('nema.ai.daily_limit_per_user');
    }

    public function used(User $user): int
    {
        return (int) Cache::get($this->key($user), 0);
    }

    public function remaining(User $user): int
    {
        return max(0, $this->limit() - $this->used($user));
    }

    /**
     * @throws AiQuotaExceededException
     */
    public function ensureAvailable(User $user): void
    {
        if ($this->remaining($user) === 0) {
            throw new AiQuotaExceededException($this->limit());
        }
    }

    /**
     * Count one provider call (cached answers and demo fallbacks are free).
     */
    public function hit(User $user): int
    {
        Cache::add($this->key($user), 0, now()->endOfDay());

        return (int) Cache::increment($this->key($user));
    }

    private function key(User $user): string
    {
        return 'ai-quota:'.$user->id.':'.now()->toDateString();
    }
}
