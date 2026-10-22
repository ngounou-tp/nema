<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;

/**
 * Providers exposing the OpenAI chat/completions format (Groq, OpenRouter).
 */
abstract class OpenAiCompatibleClient extends HttpAiClient
{
    abstract protected function endpoint(): string;

    protected function authenticate(PendingRequest $request): PendingRequest
    {
        return $request->withToken($this->apiKey());
    }

    public function chat(array $messages, array $options = []): AiResponse
    {
        $payload = [
            'model' => $this->model(),
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? (int) config('nema.ai.max_tokens'),
            'temperature' => $options['temperature'] ?? 0.3,
        ];

        if ($options['json'] ?? false) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $result = $this->post(
            'chat',
            $this->endpoint(),
            $payload,
            $options['timeout'] ?? (int) config('nema.ai.timeout'),
            fn (array $body): array => [
                (string) data_get($body, 'choices.0.message.content', ''),
                data_get($body, 'usage.total_tokens'),
            ],
        );

        return new AiResponse(
            content: $result['content'],
            provider: $this->name(),
            model: $this->model(),
            tokens: $result['tokens'],
            durationMs: $result['durationMs'],
        );
    }
}
