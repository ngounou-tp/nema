<?php

use App\Models\Producer;
use App\Models\Product;
use App\Models\User;

test('a producer only edits their own products', function () {
    $product = Product::factory()->create();
    $owner = $product->producer->user;
    $otherProducer = Producer::factory()->create()->user;

    expect($owner->can('update', $product))->toBeTrue()
        ->and($owner->can('delete', $product))->toBeTrue()
        ->and($otherProducer->can('update', $product))->toBeFalse()
        ->and($otherProducer->can('delete', $product))->toBeFalse();
});

test('a cooperative manages the products of its members only', function () {
    $memberProduct = Product::factory()->create();
    $manager = $memberProduct->producer->cooperative->manager;
    $outsiderProduct = Product::factory()->create();

    expect($manager->can('update', $memberProduct))->toBeTrue()
        ->and($manager->can('update', $outsiderProduct))->toBeFalse();
});

test('buyers read products but never edit them', function () {
    $product = Product::factory()->create();
    $buyer = User::factory()->buyer()->create();

    expect($buyer->can('view', $product))->toBeTrue()
        ->and($buyer->can('create', Product::class))->toBeFalse()
        ->and($buyer->can('update', $product))->toBeFalse();
});

test('an administrator may moderate any product', function () {
    $product = Product::factory()->create();

    expect(User::factory()->admin()->create()->can('delete', $product))->toBeTrue();
});
