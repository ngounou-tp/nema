<?php

use App\Models\User;
use App\Services\AI\AiManager;
use App\Services\AI\AiQuota;
use App\Services\AI\Exceptions\AiQuotaExceededException;
use App\Services\AI\Exceptions\AiUnavailableException;
use App\Services\AI\PromptLibrary;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    configureFakeAiProviders();
});

test('the first provider answer is returned with its metadata', function () {
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('Bonjour', 57))]);

    $response = app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]);

    expect($response->content)->toBe('Bonjour')
        ->and($response->provider)->toBe('groq')
        ->and($response->model)->toBe('groq-test-model')
        ->and($response->tokens)->toBe(57)
        ->and($response->durationMs)->toBeGreaterThanOrEqual(0)
        ->and($response->fallback)->toBeFalse();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-groq-key')
        && $request['model'] === 'groq-test-model'
        && $request['max_tokens'] === 800
        && $request['messages'] === [['role' => 'user', 'content' => 'Salut']]);
});

test('the prompt sent to the provider asks for the user language', function (string $locale, string $language) {
    app()->setLocale($locale);
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('OK'))]);

    app(AiManager::class)->chat([
        ['role' => 'system', 'content' => PromptLibrary::render(PromptLibrary::ADVISOR_V1, ['context' => '[1] Fiche cacao'])],
        ['role' => 'user', 'content' => 'Mes cabosses noircissent'],
    ]);

    Http::assertSent(fn (Request $request): bool => str_contains($request['messages'][0]['content'], "Réponds en {$language} simple"));
})->with([
    'French user' => ['fr', 'français'],
    'English user' => ['en', 'English'],
]);

test('a rate limited provider (429) hands over to the next one without retrying', function () {
    Http::fake([
        'api.groq.com/*' => Http::response(['error' => ['message' => 'Rate limit reached']], 429),
        'openrouter.ai/*' => Http::response(openAiReply('Réponse de secours')),
    ]);

    $response = app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]);

    expect($response->provider)->toBe('openrouter')
        ->and($response->content)->toBe('Réponse de secours');
    Http::assertSentCount(2);
});

test('a provider is retried once after a server error', function () {
    Sleep::fake();
    Http::fake(['api.groq.com/*' => Http::sequence()
        ->push(['error' => ['message' => 'Overloaded']], 503)
        ->push(openAiReply('Après nouvel essai')),
    ]);

    $response = app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]);

    expect($response->provider)->toBe('groq')
        ->and($response->content)->toBe('Après nouvel essai');
    Http::assertSentCount(2);
    Sleep::assertSleptTimes(1);
});

test('a timed out provider hands over to the next one', function () {
    Sleep::fake();
    Http::fake([
        'api.groq.com/*' => Http::failedConnection(),
        'openrouter.ai/*' => Http::response(openAiReply('Réponse de secours')),
    ]);

    $response = app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]);

    expect($response->provider)->toBe('openrouter');
});

test('AiUnavailableException is thrown when every provider fails', function () {
    Sleep::fake();
    Http::fake([
        'api.groq.com/*' => Http::response([], 500),
        'openrouter.ai/*' => Http::response([], 502),
    ]);

    expect(fn () => app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]))
        ->toThrow(AiUnavailableException::class, 'Every AI provider failed: groq (500), openrouter (502).');
});

test('OpenRouter is skipped when it has no key', function () {
    config(['services.openrouter.key' => null]);
    Http::fake(['api.groq.com/*' => Http::response([], 429)]);

    expect(fn () => app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]))
        ->toThrow(AiUnavailableException::class);
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'openrouter.ai'));
});

test('demo mode answers with the closest prepared answer in the current locale', function (string $locale, string $question, string $expectedTitle) {
    Sleep::fake();
    config(['nema.demo_mode' => true]);
    app()->setLocale($locale);
    Http::fake([
        'api.groq.com/*' => Http::response([], 500),
        'openrouter.ai/*' => Http::response([], 500),
    ]);

    $response = app(AiManager::class)->chat([['role' => 'user', 'content' => $question]]);

    expect($response->fallback)->toBeTrue()
        ->and($response->provider)->toBe('demo')
        ->and($response->content)->toStartWith($expectedTitle);
})->with([
    'cocoa, French' => ['fr', 'Mes cabosses de cacao ont des taches brunes', '**Pourriture brune des cabosses'],
    'cocoa, English' => ['en', 'My cocoa pods are rotting', '**Black pod rot'],
    'maize, French' => ['fr', 'Des chenilles mangent mon maïs', '**Chenille légionnaire'],
    'maize, English' => ['en', 'Caterpillars in my maize whorl', '**Fall armyworm'],
    'tomato, French' => ['fr', 'Faut-il vendre mes tomates maintenant ?', '**Évolution du prix de la tomate'],
    'tomato, English' => ['en', 'Will the tomato price go up?', '**Tomato price trend'],
]);

test('demo mode still throws when no prepared answer is related to the question', function () {
    Sleep::fake();
    config(['nema.demo_mode' => true]);
    Http::fake([
        'api.groq.com/*' => Http::response([], 500),
        'openrouter.ai/*' => Http::response([], 500),
    ]);

    expect(fn () => app(AiManager::class)->chat([['role' => 'user', 'content' => 'Comment vacciner mes poules ?']]))
        ->toThrow(AiUnavailableException::class);
});

test('a user over the daily quota is refused without calling any provider', function () {
    config(['nema.ai.daily_limit_per_user' => 2]);
    $user = User::factory()->create();
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('OK'))]);
    $manager = app(AiManager::class);

    $manager->chat([['role' => 'user', 'content' => 'Question 1']], ['user' => $user]);
    $manager->chat([['role' => 'user', 'content' => 'Question 2']], ['user' => $user]);

    expect(fn () => $manager->chat([['role' => 'user', 'content' => 'Question 3']], ['user' => $user]))
        ->toThrow(AiQuotaExceededException::class);
    Http::assertSentCount(2);
});

test('an identical request is served from the cache for 24 hours', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('Réponse mise en cache'))]);
    $manager = app(AiManager::class);
    $messages = [['role' => 'user', 'content' => 'Quand récolter le cacao ?']];

    $first = $manager->chat($messages, ['user' => $user]);
    $second = $manager->chat($messages, ['user' => $user]);

    expect($second)->toEqual($first);
    Http::assertSentCount(1);
    expect(app(AiQuota::class)->used($user))->toBe(1);

    $this->travel(24)->hours();
    $this->travel(1)->second();
    $manager->chat($messages, ['user' => $user]);

    Http::assertSentCount(2);
});

test('cached answers are not shared between French and English', function () {
    Http::fake(['api.groq.com/*' => Http::response(openAiReply('OK'))]);
    $messages = [['role' => 'user', 'content' => 'Cacao ?']];

    app()->setLocale('fr');
    app(AiManager::class)->chat($messages);
    app()->setLocale('en');
    app(AiManager::class)->chat($messages);

    Http::assertSentCount(2);
});

test('each call is logged with its metadata and never with an API key', function () {
    $logged = [];
    Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$logged) {
        $logged[] = $event;
    });
    Http::fake([
        'api.groq.com/*' => Http::response(['error' => ['message' => 'Invalid API Key test-groq-key']], 401),
        'openrouter.ai/*' => Http::response(openAiReply('OK', 12)),
    ]);

    app(AiManager::class)->chat([['role' => 'user', 'content' => 'Salut']]);

    expect($logged)->toHaveCount(2)
        ->and($logged[0]->context)->toMatchArray(['provider' => 'groq', 'model' => 'groq-test-model', 'status' => 401, 'error' => 'HTTP 401: Invalid API Key [redacted]'])
        ->and($logged[1]->context)->toMatchArray(['provider' => 'openrouter', 'model' => 'openrouter-test-model', 'tokens' => 12, 'error' => null])
        ->and($logged[1]->context['duration_ms'])->toBeInt();

    $written = json_encode(array_map(fn (MessageLogged $event): array => [$event->message, $event->context], $logged));
    expect($written)->not->toContain('test-groq-key')
        ->not->toContain('test-openrouter-key')
        ->not->toContain('Bearer');
});

test('a failed photo analysis becomes AiUnavailableException', function () {
    Sleep::fake();
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response([], 503)]);

    expect(fn () => app(AiManager::class)->analyzeImage('Analyse', 'bytes', 'image/jpeg'))
        ->toThrow(AiUnavailableException::class, 'gemini (503)');
});
