<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => app()->getLocale(),
            'locales' => config('app.supported_locales'),
            'demoMode' => (bool) config('nema.demo_mode'),
            // The product name is fixed, whatever APP_NAME says in a given environment.
            'brand' => ['name' => 'NEMA', 'logo' => $this->logoUrl()],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * The NEMA logo once it is dropped in public/images/; until then the interface shows the wordmark.
     */
    private function logoUrl(): ?string
    {
        foreach (['logo.svg', 'logo.png'] as $file) {
            if (is_file(public_path('images/'.$file))) {
                return asset('images/'.$file);
            }
        }

        return null;
    }
}
