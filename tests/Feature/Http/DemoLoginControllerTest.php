<?php

use App\Enums\UserRole;
use App\Support\DemoAccounts;
use Database\Seeders\DemoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('each demo role signs in and sees its dashboard in its saved language', function (UserRole $role, string $component, string $locale) {
    config(['nema.demo_mode' => true]);
    $this->seed(DemoSeeder::class);

    // The browser asks for the other language: the account's saved choice must win.
    $this->withHeader('Accept-Language', $locale === 'fr' ? 'en' : 'fr')
        ->post(route('demo.login', $role))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    expect(auth()->user()->email)->toBe(DemoAccounts::emailFor($role));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee("<html lang=\"{$locale}\">", escape: false)
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('locale', $locale)
            ->where('auth.user.role', $role->value));
})->with([
    'producer' => [UserRole::Producer, 'dashboard/Producer', 'fr'],
    'cooperative' => [UserRole::Cooperative, 'dashboard/Cooperative', 'fr'],
    'buyer' => [UserRole::Buyer, 'dashboard/Buyer', 'en'],
    'admin' => [UserRole::Admin, 'dashboard/Admin', 'fr'],
]);

test('switching role replaces the current demo session', function () {
    config(['nema.demo_mode' => true]);
    $this->seed(DemoSeeder::class);

    $this->post(route('demo.login', UserRole::Producer));
    $this->post(route('demo.login', UserRole::Buyer));

    expect(auth()->user()->email)->toBe(DemoAccounts::emailFor(UserRole::Buyer));
});

test('demo sign-in is refused when demo mode is off', function () {
    config(['nema.demo_mode' => false]);
    $this->seed(DemoSeeder::class);

    $this->post(route('demo.login', UserRole::Admin))->assertNotFound();

    $this->assertGuest();
});

test('demo sign-in is refused for an unknown role', function () {
    config(['nema.demo_mode' => true]);

    $this->post('/demo/login/superuser')->assertNotFound();

    $this->assertGuest();
});

test('demo sign-in is limited to 10 attempts per minute per IP', function () {
    config(['nema.demo_mode' => true]);
    $this->seed(DemoSeeder::class);

    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $this->post(route('demo.login', UserRole::Buyer))->assertRedirect(route('dashboard'));
    }

    $this->post(route('demo.login', UserRole::Buyer))->assertTooManyRequests();
});
