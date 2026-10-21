<?php

namespace Database\Factories;

use App\Enums\Filiere;
use App\Models\Cooperative;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cooperative>
 */
class CooperativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Coopérative '.fake()->unique()->lastName(),
            'region' => fake()->randomElement(['Ouest', 'Littoral', 'Centre']),
            'filieres' => fake()->randomElements(array_column(Filiere::cases(), 'value'), 3),
            'manager_id' => User::factory()->cooperative(),
        ];
    }
}
