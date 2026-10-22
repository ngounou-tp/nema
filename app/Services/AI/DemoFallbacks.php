<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;

/**
 * Prepared answers for the demo (storage/app/demo/fallbacks.json), used only
 * when every provider is down and config('nema.demo_mode') is on.
 */
class DemoFallbacks
{
    /**
     * Closest prepared answer to the last user message, in the current locale.
     * Null when no keyword matches: an unrelated prepared answer would mislead.
     *
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function match(array $messages, ?string $locale = null): ?AiResponse
    {
        $question = collect($messages)->where('role', 'user')->last()['content'] ?? null;

        if (! is_string($question) || $question === '') {
            return null;
        }

        $question = mb_strtolower($question);
        $best = null;
        $bestScore = 0;

        foreach ($this->entries() as $entry) {
            $score = collect($entry['keywords'])
                ->filter(fn (string $keyword): bool => preg_match('/(?<!\pL)'.preg_quote(mb_strtolower($keyword), '/').'(?!\pL)/u', $question) === 1)
                ->count();

            if ($score > $bestScore) {
                [$best, $bestScore] = [$entry, $score];
            }
        }

        if ($best === null) {
            return null;
        }

        $locale ??= app()->getLocale();

        return new AiResponse(
            content: $best['answers'][$locale] ?? $best['answers'][config('app.fallback_locale')] ?? $best['answers']['fr'],
            provider: 'demo',
            model: 'fallback:'.$best['id'],
            tokens: 0,
            durationMs: 0,
            fallback: true,
        );
    }

    /**
     * Entries of the JSON file; malformed entries are ignored.
     *
     * @return list<array{id: string, keywords: list<string>, answers: array<string, string>}>
     */
    private function entries(): array
    {
        return once(function (): array {
            $path = (string) config('nema.ai.fallbacks_path');
            $entries = [];

            if (! File::exists($path)) {
                return [];
            }

            foreach (File::json($path)['fallbacks'] ?? [] as $entry) {
                if (! is_string($entry['id'] ?? null) || ! is_array($entry['keywords'] ?? null) || ! is_array($entry['answers'] ?? null)) {
                    continue;
                }

                $entries[] = [
                    'id' => $entry['id'],
                    'keywords' => array_values(array_filter($entry['keywords'], is_string(...))),
                    'answers' => array_filter($entry['answers'], is_string(...)),
                ];
            }

            return $entries;
        });
    }
}
