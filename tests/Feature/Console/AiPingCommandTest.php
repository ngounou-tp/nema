<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    configureFakeAiProviders();
});

test('the ping shows the status of each provider and fails when one is down', function () {
    Sleep::fake();
    config(['services.gemini.key' => null]);
    Http::fake([
        'api.groq.com/*' => Http::response(openAiReply('pong')),
        'openrouter.ai/*' => Http::response(['error' => ['message' => 'No endpoints found']], 404),
    ]);

    $this->artisan('nema:ai-ping')
        ->expectsOutputToContain('OK')
        ->expectsOutputToContain('FAILED HTTP 404: No endpoints found')
        ->expectsOutputToContain('not configured')
        ->assertFailed();
});

test('the ping succeeds when every configured provider answers', function () {
    config(['services.openrouter.key' => null, 'services.gemini.key' => null]);
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('pong'))]);

    $this->artisan('nema:ai-ping')->assertSuccessful();
});
