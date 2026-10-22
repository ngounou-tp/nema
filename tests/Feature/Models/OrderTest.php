<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\UniqueConstraintViolationException;

test('an order links its buyer, product and review', function () {
    $order = Order::factory()->delivered()->create();
    $review = Review::factory()->for($order)->create();

    expect($order->buyer->orders->modelKeys())->toContain($order->id)
        ->and($order->product->orders->modelKeys())->toContain($order->id)
        ->and($order->review->is($review))->toBeTrue();
});

test('a delivered order exposes its status and delivery date', function () {
    $order = Order::factory()->delivered(onTime: false)->create()->fresh();

    expect($order->status)->toBe(OrderStatus::Delivered)
        ->and($order->delivered_on_time)->toBeFalse()
        ->and($order->delivered_at)->not->toBeNull();
});

test('an order can have only one review', function () {
    $order = Order::factory()->delivered()->create();
    Review::factory()->for($order)->create();

    expect(fn () => Review::factory()->for($order)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});
