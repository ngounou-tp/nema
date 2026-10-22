<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Fake keys and models for every AI provider; any request without a matching Http::fake() fails.
 */
function configureFakeAiProviders(): void
{
    Http::preventStrayRequests();

    config([
        'services.groq' => ['key' => 'test-groq-key', 'model' => 'groq-test-model'],
        'services.openrouter' => ['key' => 'test-openrouter-key', 'model' => 'openrouter-test-model'],
        'services.gemini' => ['key' => 'test-gemini-key', 'model' => 'gemini-test-model'],
        'nema.ai.text_providers' => 'groq,openrouter',
        'nema.ai.log_channel' => 'null',
        'nema.demo_mode' => false,
    ]);
}

/**
 * Body of an OpenAI-compatible chat/completions answer (Groq, OpenRouter).
 *
 * @return array<string, mixed>
 */
function openAiReply(string $content, int $tokens = 42): array
{
    return [
        'choices' => [['message' => ['role' => 'assistant', 'content' => $content], 'finish_reason' => 'stop']],
        'usage' => ['total_tokens' => $tokens],
    ];
}

/**
 * Body of a Gemini generateContent answer.
 *
 * @return array<string, mixed>
 */
function geminiReply(string $text, int $tokens = 42): array
{
    return [
        'candidates' => [['content' => ['role' => 'model', 'parts' => [['text' => $text]]], 'finishReason' => 'STOP']],
        'usageMetadata' => ['totalTokenCount' => $tokens],
    ];
}
