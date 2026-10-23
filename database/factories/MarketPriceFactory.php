<?php

namespace Database\Factories;

use App\Enums\Filiere;
use App\Enums\Region;
use App\Models\MarketPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketPrice>
 */
class MarketPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filiere' => fake()->randomElement(Filiere::cases()),
            'region' => fake()->randomElement([Region::West, Region::Littoral, Region::Centre]),
            'date' => fake()->unique()->dateTimeBetween('-10 years')->format('Y-m-d'),
            'price_xaf_kg' => fake()->numberBetween(150, 5000),
            'volume_t' => fake()->randomFloat(2, 10, 2000),
        ];
    }
}
