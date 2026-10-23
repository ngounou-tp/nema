<?php

use App\Enums\Filiere;
use App\Enums\OrderStatus;
use App\Models\AiMessage;
use App\Models\Cooperative;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Review;
use App\Models\SupplierScore;
use App\Models\User;
use App\Services\DashboardService;

test('the producer dashboard counts only their own products and open orders', function () {
    $producer = Producer::factory()->create();
    $product = Product::factory()->for($producer)->create();
    Product::factory()->for($producer)->unavailable()->create();
    Order::factory()->for($product)->create(['status' => OrderStatus::Pending]);
    Order::factory()->for($product)->create(['status' => OrderStatus::Confirmed]);
    Order::factory()->for($product)->delivered()->create();
    Order::factory()->create();
    SupplierScore::factory()->for($producer)->create(['score' => 86.6]);

    $dashboard = app(DashboardService::class)->forProducer($producer->user);

    expect($dashboard['products']['total'])->toBe(2)
        ->and($dashboard['products']['available'])->toBe(1)
        ->and($dashboard['ordersInProgress'])->toHaveCount(2)
        ->and($dashboard['score']['value'])->toBe(87)
        ->and($dashboard['diagnoses'])->toBe([]);
});

test('the producer dashboard has no score until one is computed', function () {
    $producer = Producer::factory()->create();

    expect(app(DashboardService::class)->forProducer($producer->user)['score'])->toBeNull();
});

test('the cooperative dashboard adds up the stock of its own members only', function () {
    $cooperative = Cooperative::factory()->create();
    $member = Producer::factory()->for($cooperative)->create();
    Product::factory()->for($member)->create(['filiere' => Filiere::Cacao, 'quantity_kg' => 1200]);
    Product::factory()->for(Producer::factory()->for($cooperative))->create(['filiere' => Filiere::Cacao, 'quantity_kg' => 300]);
    Product::factory()->for($member)->unavailable()->create(['filiere' => Filiere::Manioc, 'quantity_kg' => 900]);
    Product::factory()->create(['filiere' => Filiere::Cacao, 'quantity_kg' => 5000]);

    $order = Order::factory()->for(Product::factory()->for($member)->unavailable())->create(['total_xaf' => 250_000]);
    Order::factory()->for($order->product)->create(['status' => OrderStatus::Cancelled, 'total_xaf' => 99_000]);
    Order::factory()->for($order->product)->create(['created_at' => now()->subMonths(2)]);

    $dashboard = app(DashboardService::class)->forCooperative($cooperative->manager);

    expect($dashboard['members']['total'])->toBe(2)
        ->and($dashboard['stock'])->toBe([
            ['filiere' => 'cocoa', 'quantityKg' => 1500.0],
        ])
        ->and($dashboard['ordersThisMonth']['count'])->toBe(2)
        ->and($dashboard['ordersThisMonth']['totalXaf'])->toBe(250_000);
});

test('the buyer dashboard ranks suppliers with at least three reviews by average rating', function () {
    $reviewed = function (int ...$ratings): Producer {
        $producer = Producer::factory()->create();

        foreach ($ratings as $rating) {
            Review::factory()
                ->for(Order::factory()->delivered()->for(Product::factory()->for($producer)))
                ->create(['rating' => $rating]);
        }

        return $producer;
    };

    $best = $reviewed(5, 5, 4);
    $second = $reviewed(4, 4, 4, 3);
    $reviewed(5, 5);

    $buyer = User::factory()->buyer()->create();
    Order::factory()->for($buyer, 'buyer')->count(2)->create();

    $dashboard = app(DashboardService::class)->forBuyer($buyer);

    expect(array_column($dashboard['topSuppliers'], 'id'))->toBe([$best->id, $second->id])
        ->and($dashboard['topSuppliers'][0]['averageRating'])->toBe(4.7)
        ->and($dashboard['topSuppliers'][0]['reviewsCount'])->toBe(3)
        ->and($dashboard['recentOrders'])->toHaveCount(2)
        ->and($dashboard['ordersInProgress'])->toBe(2);
});

test('the admin dashboard counts assistant answers of the last 7 days, day by day', function () {
    $this->freezeTime();
    AiMessage::factory()->assistant()->count(2)->create();
    AiMessage::factory()->assistant()->create(['created_at' => now()->subDays(6)]);
    AiMessage::factory()->assistant()->create(['created_at' => now()->subDays(7)]);
    AiMessage::factory()->create();

    $aiCalls = app(DashboardService::class)->forAdmin()['aiCalls'];

    expect($aiCalls['total'])->toBe(3)
        ->and($aiCalls['days'])->toHaveCount(7)
        ->and($aiCalls['days'][0])->toBe(['date' => now()->subDays(6)->toDateString(), 'count' => 1])
        ->and($aiCalls['days'][6])->toBe(['date' => now()->toDateString(), 'count' => 2]);
});

test('the admin dashboard counts users per role and orders per status', function () {
    User::factory()->buyer()->count(2)->create();
    Order::factory()->delivered()->create();

    $dashboard = app(DashboardService::class)->forAdmin();

    expect($dashboard['users']['byRole']['buyer'])->toBe(3)
        ->and($dashboard['users']['total'])->toBe(User::count())
        ->and($dashboard['orders']['byStatus'])->toBe(['pending' => 0, 'confirmed' => 0, 'delivered' => 1, 'cancelled' => 0]);
});
