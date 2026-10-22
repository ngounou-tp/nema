<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AiProviderException;
use App\Services\AI\Exceptions\InvalidAiJsonException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Google Gemini: vision provider (photo diagnosis), usable for text too.
 */
class GeminiClient extends HttpAiClient
{
    protected function configKey(): string
    {
        return 'gemini';
    }

    protected function authenticate(PendingRequest $request): PendingRequest
    {
        return $request->withHeaders(['x-goog-api-key' => $this->apiKey()]);
    }

    public function chat(array $messages, array $options = []): AiResponse
    {
        $system = collect($messages)->where('role', 'system')->pluck('content')->implode("\n\n");

        $payload = [
            'contents' => collect($messages)
                ->reject(fn (array $message): bool => $message['role'] === 'system')
                ->map(fn (array $message): array => [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $message['content']]],
                ])
                ->values()
                ->all(),
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? (int) config('nema.ai.max_tokens'),
                'temperature' => $options['temperature'] ?? 0.3,
            ],
        ];

        if ($system !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }

        if ($options['json'] ?? false) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $result = $this->post(
            'chat',
            $this->endpoint(),
            $payload,
            $options['timeout'] ?? (int) config('nema.ai.timeout'),
            fn (array $body): array => [$this->text($body), data_get($body, 'usageMetadata.totalTokenCount')],
        );

        return new AiResponse(
            content: $result['content'],
            provider: $this->name(),
            model: $this->model(),
            tokens: $result['tokens'],
            durationMs: $result['durationMs'],
        );
    }

    /**
     * Analyse a photo and return the JSON object the prompt asks for.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidAiJsonException when the answer is not a JSON object
     * @throws AiProviderException
     */
    public function analyzeImage(string $prompt, string $bytes, string $mime): array
    {
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode($bytes)]],
                ],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => (int) config('nema.ai.vision_max_tokens'),
                'temperature' => 0.2,
            ],
        ];

        $decoded = [];

        $this->post(
            'vision',
            $this->endpoint(),
            $payload,
            (int) config('nema.ai.vision_timeout'),
            function (array $body) use (&$decoded): array {
                $text = $this->text($body);

                if (trim($text) !== '') {
                    $decoded = $this->decodeObject($text);
                }

                return [$text, data_get($body, 'usageMetadata.totalTokenCount')];
            },
        );

        return $decoded;
    }

    private function endpoint(): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($this->model()).':generateContent';
    }

    /**
     * Answer text, without the model's "thought" parts.
     *
     * @param  array<string, mixed>  $body
     */
    private function text(array $body): string
    {
        $parts = data_get($body, 'candidates.0.content.parts');
        $text = '';

        foreach (is_array($parts) ? $parts : [] as $part) {
            if (is_array($part) && ($part['thought'] ?? false) !== true && is_string($part['text'] ?? null)) {
                $text .= $part['text'];
            }
        }

        return $text;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidAiJsonException
     */
    private function decodeObject(string $text): array
    {
        // Some models still wrap JSON in a Markdown fence despite responseMimeType.
        $json = preg_replace('/^\s*```(?:json)?\s*|\s*```\s*$/', '', $text);

        $decoded = json_decode((string) $json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidAiJsonException($this->name(), json_last_error_msg());
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidAiJsonException($this->name(), 'a JSON object was expected');
        }

        return $decoded;
    }

    protected function finishReason(Response $response): string
    {
        return (string) ($response->json('candidates.0.finishReason') ?? $response->json('promptFeedback.blockReason') ?? 'unknown');
    }
}
