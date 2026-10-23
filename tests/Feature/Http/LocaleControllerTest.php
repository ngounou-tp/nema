<?php

use App\Enums\Locale;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a guest switch of language persists after reloading the page', function () {
    $this->from(route('home'))
        ->put(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('a signed-in switch is saved on the account and survives a new login', function () {
    $user = User::factory()->create(['locale' => Locale::French]);

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->put(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->locale)->toBe(Locale::English);

    // New session: logged out, the browser asks for French, then the user signs in again.
    $this->post(route('logout'));
    $this->flushSession();

    $this->withHeader('Accept-Language', 'fr')
        ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('an unsupported language is rejected', function () {
    $this->from(route('home'))
        ->put(route('locale.update'), ['locale' => 'de'])
        ->assertSessionHasErrors('locale');
});

test('validation messages come back in the current language', function () {
    $this->from(route('home'))
        ->withSession(['locale' => 'fr'])
        ->put(route('locale.update'), ['locale' => ''])
        ->assertSessionHasErrors(['locale' => __('validation.required', ['attribute' => 'locale'], 'fr')]);
});
