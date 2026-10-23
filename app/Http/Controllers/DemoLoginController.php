<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\DemoAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    /**
     * "Essayer en tant que…": sign in as the demo account of a role, in one click.
     * Only exists in demo mode; switching role simply replaces the session.
     */
    public function store(Request $request, UserRole $role): RedirectResponse
    {
        abort_unless(config('nema.demo_mode'), 404);

        $user = User::query()->where('email', DemoAccounts::emailFor($role))->first();

        abort_if($user === null, 404);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
