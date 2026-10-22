<?php

use App\Services\AI\Exceptions\InvalidAiJsonException;
use App\Services\AI\GeminiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    configureFakeAiProviders();
});

test('a photo is sent inline and the diagnosis JSON is decoded', function () {
    $diagnosis = [
        'culture' => 'cacao',
        'symptomes' => ['taches brunes sur la cabosse'],
        'hypotheses' => [['probleme' => 'pourriture brune', 'confiance' => 0.82]],
        'actions_immediates' => ['retirer les cabosses malades'],
        'prevention' => ['récolter souvent'],
        'consulter_technicien' => true,
        'image_exploitable' => true,
    ];
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply(json_encode($diagnosis)))]);

    $result = app(GeminiClient::class)->analyzeImage('Analyse cette photo', 'jpeg-bytes', 'image/jpeg');

    expect($result)->toBe($diagnosis);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test-model:generateContent'
        && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
        && ! $request->hasHeader('Authorization')
        && $request['contents'][0]['parts'][0] === ['text' => 'Analyse cette photo']
        && $request['contents'][0]['parts'][1] === ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => base64_encode('jpeg-bytes')]]
        && $request['generationConfig']['responseMimeType'] === 'application/json');
});

test('a diagnosis wrapped in a Markdown fence is still decoded', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply("```json\n{\"culture\": \"maïs\"}\n```"))]);

    expect(app(GeminiClient::class)->analyzeImage('Analyse', 'bytes', 'image/png'))->toBe(['culture' => 'maïs']);
});

test('an invalid diagnosis JSON throws InvalidAiJsonException', function (string $answer) {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply($answer))]);

    expect(fn () => app(GeminiClient::class)->analyzeImage('Analyse', 'bytes', 'image/jpeg'))
        ->toThrow(InvalidAiJsonException::class);
})->with([
    'plain text' => ['Je pense que c\'est la pourriture brune.'],
    'truncated object' => ['{"culture": "cacao", "symptomes": ['],
    'list instead of object' => ['["cacao"]'],
]);

test('text chat moves system messages to the system instruction', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply('Réponse', 30))]);

    $response = app(GeminiClient::class)->chat([
        ['role' => 'system', 'content' => 'Tu es NEMA.'],
        ['role' => 'user', 'content' => 'Bonjour'],
        ['role' => 'assistant', 'content' => 'Bonjour !'],
        ['role' => 'user', 'content' => 'Merci'],
    ]);

    expect($response->content)->toBe('Réponse')
        ->and($response->provider)->toBe('gemini')
        ->and($response->tokens)->toBe(30);
    Http::assertSent(fn (Request $request): bool => $request['systemInstruction'] === ['parts' => [['text' => 'Tu es NEMA.']]]
        && array_column($request['contents'], 'role') === ['user', 'model', 'user']);
});
