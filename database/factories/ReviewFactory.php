<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->delivered(),
            'rating' => fake()->numberBetween(1, 5),
            'quality_rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
            'has_complaint' => false,
        ];
    }
}
