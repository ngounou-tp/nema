<?php

namespace Database\Factories;

use App\Enums\Filiere;
use App\Enums\QualityGrade;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filiere = fake()->randomElement(Filiere::cases());

        return [
            'producer_id' => Producer::factory(),
            'filiere' => $filiere,
            'name' => $filiere->label().' '.fake()->word(),
            'description' => fake()->sentence(),
            'quantity_kg' => fake()->numberBetween(50, 5000),
            'price_xaf' => fake()->numberBetween(150, 5000),
            'quality_grade' => fake()->randomElement(QualityGrade::cases()),
            'is_available' => true,
        ];
    }

    /**
     * Indicate that the product is no longer available.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
