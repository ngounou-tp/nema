<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
class AiMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => AiConversation::factory(),
            'role' => MessageRole::User,
            'content' => fake()->sentence(),
            'provider' => null,
            'tokens' => null,
            'duration_ms' => null,
            'sources' => null,
            'error' => null,
        ];
    }

    /**
     * Indicate that the message is an assistant answer.
     */
    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::Assistant,
            'provider' => 'groq',
            'tokens' => fake()->numberBetween(100, 800),
            'duration_ms' => fake()->numberBetween(300, 4000),
            'sources' => [['index' => 1, 'source' => 'KisanVaani']],
        ]);
    }
}
