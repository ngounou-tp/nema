<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producer>
 */
class ProducerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->producer(),
            'cooperative_id' => Cooperative::factory(),
            'village' => fake()->randomElement(['Dschang', 'Foto', 'Penja', 'Njombé', 'Obala', 'Mbalmayo']),
            'surface_ha' => fake()->randomFloat(2, 0.5, 15),
        ];
    }

    /**
     * Indicate that the producer does not belong to a cooperative.
     */
    public function independent(): static
    {
        return $this->state(fn (array $attributes) => [
            'cooperative_id' => null,
        ]);
    }
}
