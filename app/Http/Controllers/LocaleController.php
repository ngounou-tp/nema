<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Switch the interface language: kept in the session, and in users.locale when signed in.
     */
    public function update(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');

        $request->session()->put(SetLocale::SESSION_KEY, $locale);
        $request->user()?->update(['locale' => $locale]);

        return back();
    }
}
