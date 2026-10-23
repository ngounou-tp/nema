<?php

use App\Enums\Locale;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the interface is in French by default', function () {
    // Laravel test requests send "Accept-Language: en-us" unless told otherwise.
    $this->withHeader('Accept-Language', '')
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});

test('a guest gets the language of the browser', function () {
    $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('an unsupported browser language falls back to French', function () {
    $this->withHeader('Accept-Language', 'de-DE')
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});

test('?lang on a public page wins over the browser and is remembered', function () {
    $this->withHeader('Accept-Language', 'fr')
        ->get(route('home', ['lang' => 'en']))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'))
        ->assertSee('<html lang="en">', escape: false);

    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('?lang is ignored on signed-in pages, where the saved preference applies', function () {
    $user = User::factory()->create(['locale' => Locale::French]);

    $this->actingAs($user)
        ->get(route('dashboard', ['lang' => 'en']))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});

test('the saved preference of a user wins over the session and the browser', function () {
    $user = User::factory()->english()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'fr'])
        ->withHeader('Accept-Language', 'fr')
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('an unknown ?lang value is ignored', function () {
    $this->withHeader('Accept-Language', '')
        ->get(route('home', ['lang' => 'de']))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});
