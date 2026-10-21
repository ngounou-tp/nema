<?php

use App\Enums\Locale;
use App\Enums\UserRole;
use App\Models\Cooperative;
use App\Models\Order;
use App\Models\Producer;
use App\Models\User;

test('a user created without a role or language is a French-speaking buyer', function () {
    $user = User::query()->forceCreate([
        'name' => 'Arlette Ndjock',
        'email' => 'arlette@example.com',
        'password' => 'secret-password',
    ]);

    expect($user->fresh()->role)->toBe(UserRole::Buyer)
        ->and($user->fresh()->locale)->toBe(Locale::French)
        ->and($user->fresh()->hasRole(UserRole::Buyer))->toBeTrue()
        ->and($user->fresh()->hasRole(UserRole::Admin))->toBeFalse();
});

test('a producer account has its producer profile', function () {
    $producer = Producer::factory()->create();

    expect($producer->user->producer->is($producer))->toBeTrue()
        ->and($producer->user->role)->toBe(UserRole::Producer);
});

test('a cooperative account manages its cooperative', function () {
    $cooperative = Cooperative::factory()->create();

    expect($cooperative->manager->managedCooperative->is($cooperative))->toBeTrue();
});

test('a buyer sees only the orders they placed', function () {
    $buyer = User::factory()->buyer()->create();
    $ownOrders = Order::factory()->count(2)->for($buyer, 'buyer')->create();
    Order::factory()->create();

    expect($buyer->orders->modelKeys())->toEqualCanonicalizing($ownOrders->modelKeys());
});
