<?php

namespace Database\Factories;

use App\Enums\LotStep;
use App\Models\Lot;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lot>
 */
class LotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $harvestDate = fake()->dateTimeBetween('-6 months', '-1 week');

        return [
            'product_id' => Product::factory(),
            'harvest_date' => $harvestDate,
            'origin' => fake()->randomElement(['Dschang, Ouest', 'Penja, Littoral', 'Obala, Centre']),
            'steps' => [
                ['label' => LotStep::Harvest->value, 'date' => $harvestDate->format('Y-m-d'), 'location' => 'Parcelle du producteur'],
                ['label' => LotStep::Drying->value, 'date' => (clone $harvestDate)->modify('+3 days')->format('Y-m-d'), 'location' => 'Aire de séchage'],
                ['label' => LotStep::Storage->value, 'date' => (clone $harvestDate)->modify('+10 days')->format('Y-m-d'), 'location' => 'Magasin de la coopérative'],
            ],
            'qr_path' => null,
        ];
    }
}
