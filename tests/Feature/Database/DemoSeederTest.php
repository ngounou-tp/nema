<?php

use App\Enums\Filiere;
use App\Enums\Locale;
use App\Enums\LotStep;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Cooperative;
use App\Models\Lot;
use App\Models\MarketPrice;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Hash;

test('it creates the four demo accounts with their role and the shared password', function () {
    $this->seed(DemoSeeder::class);

    $accounts = [
        'producteur@nema.demo' => UserRole::Producer,
        'coop@nema.demo' => UserRole::Cooperative,
        'acheteur@nema.demo' => UserRole::Buyer,
        'admin@nema.demo' => UserRole::Admin,
    ];

    foreach ($accounts as $email => $role) {
        $user = User::query()->where('email', $email)->sole();

        expect($user->role)->toBe($role)
            ->and(Hash::check('nema2026', $user->password))->toBeTrue()
            ->and($user->email_verified_at)->not->toBeNull();
    }
});

test('it creates the expected volume of demo data', function () {
    $this->seed(DemoSeeder::class);

    expect(Cooperative::count())->toBe(3)
        ->and(Producer::count())->toBe(25)
        ->and(Product::count())->toBe(60)
        ->and(Order::count())->toBe(120)
        ->and(Lot::count())->toBe(20)
        ->and(MarketPrice::count())->toBe(count(Filiere::cases()) * 3 * 12)
        ->and(MarketPrice::min('date'))->toStartWith('2025-11-01')
        ->and(MarketPrice::max('date'))->toStartWith('2026-10-01');
});

test('every lot has between 4 and 6 traceability steps', function () {
    $this->seed(DemoSeeder::class);

    Lot::all()->each(fn (Lot $lot) => expect(count($lot->steps))->toBeBetween(4, 6));
});

test('lot steps are stored as translatable keys', function () {
    $this->seed(DemoSeeder::class);

    $labels = Lot::all()->flatMap(fn (Lot $lot): array => array_column($lot->steps, 'label'))->unique();

    expect($labels->reject(fn (string $label): bool => LotStep::tryFrom($label) !== null))->toBeEmpty();
});

test('the demo accounts use their planned language', function () {
    $this->seed(DemoSeeder::class);

    $locales = User::query()->whereIn('email', DemoSeeder::DEMO_ACCOUNTS)->pluck('locale', 'email');

    expect($locales->all())->toEqual([
        'producteur@nema.demo' => Locale::French,
        'coop@nema.demo' => Locale::French,
        'acheteur@nema.demo' => Locale::English,
        'admin@nema.demo' => Locale::French,
    ]);
});

test('the demo producer is a reliable cacao farmer of the demo cooperative', function () {
    $this->seed(DemoSeeder::class);

    $producer = User::query()->where('email', 'producteur@nema.demo')->sole()->producer;
    $orders = $producer->orders;

    expect($producer->cooperative->manager->email)->toBe('coop@nema.demo')
        ->and($producer->products->pluck('filiere'))->toContain(Filiere::Cacao)
        ->and($producer->lots)->not->toBeEmpty()
        ->and($orders->every(fn (Order $order) => $order->status === OrderStatus::Delivered && $order->delivered_on_time))->toBeTrue();
});

test('running it twice does not duplicate the demo data', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(User::query()->where('email', 'admin@nema.demo')->count())->toBe(1)
        ->and(Producer::count())->toBe(25)
        ->and(Order::count())->toBe(120)
        ->and(MarketPrice::count())->toBe(count(Filiere::cases()) * 3 * 12);
});

test('it keeps accounts that are not part of the demo', function () {
    $realUser = User::factory()->create(['email' => 'cooperative@example.cm']);

    $this->seed(DemoSeeder::class);

    expect($realUser->fresh())->not->toBeNull();
});
