<?php

use App\Models\Lot;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\SupplierScore;
use Illuminate\Database\UniqueConstraintViolationException;

test('a producer receives the orders placed on their own products only', function () {
    $producer = Producer::factory()->create();
    $products = Product::factory()->count(2)->for($producer)->create();
    $ownOrders = $products->flatMap(fn (Product $product) => Order::factory()->count(2)->for($product)->create());
    Order::factory()->create();

    expect($producer->orders->modelKeys())->toEqualCanonicalizing($ownOrders->pluck('id')->all());
});

test('a producer reaches the lots of their products', function () {
    $producer = Producer::factory()->create();
    $lot = Lot::factory()->for(Product::factory()->for($producer))->create();
    Lot::factory()->create();

    expect($producer->lots->modelKeys())->toBe([$lot->id]);
});

test('a producer has a single supplier score', function () {
    $producer = Producer::factory()->create();
    SupplierScore::factory()->for($producer)->create();

    expect(fn () => SupplierScore::factory()->for($producer)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

test('deleting a producer account removes their products and orders', function () {
    $producer = Producer::factory()->create();
    $order = Order::factory()->for(Product::factory()->for($producer))->create();

    $producer->user->delete();

    expect(Producer::find($producer->id))->toBeNull()
        ->and(Order::find($order->id))->toBeNull();
});
