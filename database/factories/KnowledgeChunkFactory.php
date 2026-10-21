<?php

namespace Database\Factories;

use App\Models\KnowledgeChunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeChunk>
 */
class KnowledgeChunkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => fake()->randomElement(['KisanVaani/agriculture-qa-english-only', 'DigiGreen/Kenya_Agri_queries']),
            'title' => fake()->sentence(5),
            'content' => fake()->paragraphs(3, true),
            'embedding' => null,
        ];
    }
}
