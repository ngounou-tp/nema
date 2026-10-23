<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the interface language, in this order: ?lang (public pages only),
 * the user's saved preference (users.locale), the session, the browser's
 * Accept-Language header, then French.
 */
class SetLocale
{
    public const string SESSION_KEY = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);
        Date::setLocale($locale);

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $fromQuery = $this->supported($request->query('lang'));

        if ($fromQuery !== null && $this->isPublic($request)) {
            $request->session()->put(self::SESSION_KEY, $fromQuery);

            return $fromQuery;
        }

        return $request->user()->locale->value
            ?? $this->supported($request->session()->get(self::SESSION_KEY))
            ?? $this->fromBrowser($request)
            ?? Locale::French->value;
    }

    /**
     * A page is public when its route does not require authentication.
     */
    private function isPublic(Request $request): bool
    {
        return ! in_array('auth', $request->route()?->gatherMiddleware() ?? [], true);
    }

    private function fromBrowser(Request $request): ?string
    {
        if ($request->headers->get('Accept-Language') === null) {
            return null;
        }

        return $this->supported($request->getPreferredLanguage(config('app.supported_locales')));
    }

    private function supported(mixed $locale): ?string
    {
        return is_string($locale) && in_array($locale, config('app.supported_locales'), true) ? $locale : null;
    }
}
