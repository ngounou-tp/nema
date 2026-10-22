<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('the ai rate limiter allows 10 requests per minute per user', function () {
    Route::middleware(['web', 'auth', 'throttle:ai'])->get('/_test/ai', fn () => 'ok');
    [$user, $otherUser] = User::factory()->count(2)->create();

    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $this->actingAs($user)->get('/_test/ai')->assertOk();
    }

    $this->actingAs($user)->get('/_test/ai')->assertTooManyRequests();
    $this->actingAs($otherUser)->get('/_test/ai')->assertOk();
});
