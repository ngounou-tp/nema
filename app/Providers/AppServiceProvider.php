<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
        $this->configureAuthorization();
    }

    /**
     * "ai" limiter for the AI routes (`throttle:ai`): per user, by IP for guests.
     * The daily cap is App\Services\AI\AiQuota.
     * "demo-login" limits the one-click jury sign-in, per IP.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('ai', fn (Request $request): Limit => Limit::perMinute((int) config('nema.ai.per_minute_per_user'))
            ->by($request->user()->id ?? $request->ip()));

        RateLimiter::for('demo-login', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
    }

    /**
     * Administrators moderate the whole platform: every policy check passes for them.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(fn (User $user): ?bool => $user->hasRole(UserRole::Admin) ? true : null);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
