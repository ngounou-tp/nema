<?php

use App\Enums\UserRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a buyer cannot open the admin pages', function (string $route) {
    $buyer = User::factory()->buyer()->create();

    $this->actingAs($buyer)
        ->get(route($route))
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 403));
})->with(['admin.moderation', 'admin.ai-log']);

test('an administrator opens the admin pages', function (string $route) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route($route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('ComingSoon'));
})->with(['admin.moderation', 'admin.ai-log']);

test('each module page is open to its roles only', function (string $route, array $allowed) {
    foreach (UserRole::cases() as $role) {
        $response = $this->actingAs(User::factory()->role($role)->create())->get(route($route));

        in_array($role, $allowed, true) ? $response->assertOk() : $response->assertForbidden();
    }
})->with([
    'assistant' => ['assistant.index', [UserRole::Producer, UserRole::Cooperative, UserRole::Admin]],
    'diagnosis' => ['diagnosis.index', [UserRole::Producer, UserRole::Cooperative, UserRole::Admin]],
    'marketplace' => ['marketplace.index', UserRole::cases()],
    'traceability' => ['traceability.index', UserRole::cases()],
    'forecasts' => ['forecasts.index', UserRole::cases()],
    'cooperative' => ['cooperative.index', [UserRole::Cooperative, UserRole::Admin]],
]);

test('module pages show a clean coming-soon page', function () {
    $this->actingAs(User::factory()->producer()->create())
        ->get(route('diagnosis.index'))
        ->assertInertia(fn (Assert $page) => $page->component('ComingSoon')->where('module', 'diagnosis'));
});

test('guests are sent to the login page', function () {
    $this->get(route('marketplace.index'))->assertRedirect(route('login'));
});
