<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityKg = fake()->numberBetween(20, 1000);

        return [
            'buyer_id' => User::factory()->buyer(),
            'product_id' => Product::factory(),
            'quantity_kg' => $quantityKg,
            'total_xaf' => $quantityKg * fake()->numberBetween(150, 5000),
            'status' => OrderStatus::Pending,
            'delivered_on_time' => null,
            'delivered_at' => null,
        ];
    }

    /**
     * Indicate that the order was delivered.
     */
    public function delivered(bool $onTime = true): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'delivered_on_time' => $onTime,
            'delivered_at' => now(),
        ]);
    }
}
