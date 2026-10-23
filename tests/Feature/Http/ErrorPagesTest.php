<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('an unknown address shows the NEMA 404 page in the visitor language', function () {
    $this->withHeader('Accept-Language', 'en')
        ->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 404)
            ->where('locale', 'en'));
});

test('the 404 page follows the language saved on the account', function () {
    $this->actingAs(User::factory()->english()->create())
        ->get('/this-page-does-not-exist')
        ->assertInertia(fn (Assert $page) => $page->component('Error')->where('locale', 'en'));
});

test('too many requests show the NEMA 429 page', function () {
    Route::middleware(['web', 'throttle:1,1'])->get('/_test/throttled', fn () => 'ok');

    $this->get('/_test/throttled')->assertOk();
    $this->get('/_test/throttled')
        ->assertTooManyRequests()
        ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 429));
});

test('server errors show the NEMA 500 page when debug is off', function () {
    config(['app.debug' => false]);
    Route::middleware('web')->get('/_test/broken', fn () => throw new RuntimeException('Boom'));

    $this->get('/_test/broken')
        ->assertInternalServerError()
        ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 500))
        ->assertDontSee('Boom');
});

test('JSON requests keep JSON errors', function () {
    $this->getJson('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
