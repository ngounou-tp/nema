<?php

use App\Models\Order;
use App\Models\Review;
use App\Models\User;

test('an order is seen by its buyer and its seller only', function () {
    $order = Order::factory()->create();

    expect($order->buyer->can('view', $order))->toBeTrue()
        ->and($order->product->producer->user->can('view', $order))->toBeTrue()
        ->and($order->product->producer->cooperative->manager->can('view', $order))->toBeTrue()
        ->and(User::factory()->buyer()->create()->can('view', $order))->toBeFalse();
});

test('only the seller side updates an order', function () {
    $order = Order::factory()->create();

    expect($order->product->producer->user->can('update', $order))->toBeTrue()
        ->and($order->buyer->can('update', $order))->toBeFalse();
});

test('only buyers place orders', function () {
    expect(User::factory()->buyer()->create()->can('create', Order::class))->toBeTrue()
        ->and(User::factory()->producer()->create()->can('create', Order::class))->toBeFalse();
});

test('the buyer reviews a delivered order once', function () {
    $delivered = Order::factory()->delivered()->create();
    $pending = Order::factory()->create();

    expect($delivered->buyer->can('create', [Review::class, $delivered]))->toBeTrue()
        ->and($pending->buyer->can('create', [Review::class, $pending]))->toBeFalse()
        ->and(User::factory()->buyer()->create()->can('create', [Review::class, $delivered]))->toBeFalse();

    Review::factory()->for($delivered)->create();

    expect($delivered->buyer->can('create', [Review::class, $delivered]))->toBeFalse();
});
