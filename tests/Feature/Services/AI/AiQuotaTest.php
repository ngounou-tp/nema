<?php

use App\Models\User;
use App\Services\AI\AiQuota;
use App\Services\AI\Exceptions\AiQuotaExceededException;

test('the daily quota blocks a user at the limit and resets the next day', function () {
    config(['nema.ai.daily_limit_per_user' => 2]);
    $this->travelTo(now()->setTime(23, 59));
    $user = User::factory()->create();
    $quota = app(AiQuota::class);

    $quota->hit($user);
    $quota->hit($user);

    expect($quota->remaining($user))->toBe(0)
        ->and(fn () => $quota->ensureAvailable($user))->toThrow(AiQuotaExceededException::class);

    $this->travel(2)->minutes();

    expect($quota->remaining($user))->toBe(2);
});

test('each user has their own quota', function () {
    config(['nema.ai.daily_limit_per_user' => 1]);
    [$first, $second] = User::factory()->count(2)->create();
    $quota = app(AiQuota::class);

    $quota->hit($first);

    expect($quota->remaining($first))->toBe(0)
        ->and($quota->remaining($second))->toBe(1);
});
