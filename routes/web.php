<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

// "Essayer en tant que…": jury access by role, only when config('nema.demo_mode') is on.
Route::post('demo/login/{role}', [DemoLoginController::class, 'store'])
    ->middleware('throttle:demo-login')
    ->name('demo.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Modules built in the next tasks: named routes and a clean "coming soon" page for now.
    Route::inertia('assistant', 'ComingSoon', ['module' => 'assistant'])
        ->middleware('role:producer,cooperative,admin')->name('assistant.index');
    Route::inertia('diagnosis', 'ComingSoon', ['module' => 'diagnosis'])
        ->middleware('role:producer,cooperative,admin')->name('diagnosis.index');
    Route::inertia('marketplace', 'ComingSoon', ['module' => 'marketplace'])->name('marketplace.index');
    Route::inertia('traceability', 'ComingSoon', ['module' => 'traceability'])->name('traceability.index');
    Route::inertia('forecasts', 'ComingSoon', ['module' => 'forecasts'])->name('forecasts.index');
    Route::inertia('cooperative', 'ComingSoon', ['module' => 'cooperative'])
        ->middleware('role:cooperative,admin')->name('cooperative.index');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::inertia('moderation', 'ComingSoon', ['module' => 'moderation'])->name('moderation');
        Route::inertia('ai-log', 'ComingSoon', ['module' => 'ai_log'])->name('ai-log');
    });
});

require __DIR__.'/settings.php';

// Unknown URLs still go through the web middleware (session, language) to get the NEMA 404 page.
Route::fallback(fn () => abort(404));
