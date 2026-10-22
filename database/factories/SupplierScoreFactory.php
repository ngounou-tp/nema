<?php

namespace Database\Factories;

use App\Models\Producer;
use App\Models\SupplierScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierScore>
 */
class SupplierScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producer_id' => Producer::factory(),
            'score' => fake()->randomFloat(2, 30, 100),
            'details' => [
                'on_time_rate' => fake()->randomFloat(2, 0, 1),
                'quality_average' => fake()->randomFloat(2, 1, 5),
                'volume_regularity' => fake()->randomFloat(2, 0, 1),
                'complaint_rate' => fake()->randomFloat(2, 0, 1),
                'seniority_months' => fake()->numberBetween(0, 24),
            ],
            'computed_at' => now(),
        ];
    }
}
