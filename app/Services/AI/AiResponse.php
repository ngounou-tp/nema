<?php

namespace App\Services\AI;

/**
 * A text answer from an AI provider (or from the demo fallbacks).
 */
final readonly class AiResponse
{
    public function __construct(
        public string $content,
        public string $provider,
        public string $model,
        public ?int $tokens,
        public int $durationMs,
        public bool $fallback = false,
    ) {}

    /**
     * @return array{content: string, provider: string, model: string, tokens: int|null, durationMs: int, fallback: bool}
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'tokens' => $this->tokens,
            'durationMs' => $this->durationMs,
            'fallback' => $this->fallback,
        ];
    }

    /**
     * Rebuild a response stored by toArray(); null when the value has another shape
     * (e.g. a cache entry written by an older version).
     */
    public static function fromArray(mixed $attributes): ?self
    {
        if (! is_array($attributes)
            || ! is_string($attributes['content'] ?? null)
            || ! is_string($attributes['provider'] ?? null)
            || ! is_string($attributes['model'] ?? null)
            || ! (is_int($attributes['tokens'] ?? null) || ($attributes['tokens'] ?? null) === null)
            || ! is_int($attributes['durationMs'] ?? null)) {
            return null;
        }

        return new self(
            content: $attributes['content'],
            provider: $attributes['provider'],
            model: $attributes['model'],
            tokens: $attributes['tokens'],
            durationMs: $attributes['durationMs'],
            fallback: ($attributes['fallback'] ?? false) === true,
        );
    }
}
