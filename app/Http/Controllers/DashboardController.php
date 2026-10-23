<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * One dashboard per role, with real figures from the database.
     */
    public function __invoke(Request $request, DashboardService $dashboards): Response
    {
        $user = $request->user();

        return match ($user->role) {
            UserRole::Producer => Inertia::render('dashboard/Producer', $dashboards->forProducer($user)),
            UserRole::Cooperative => Inertia::render('dashboard/Cooperative', $dashboards->forCooperative($user)),
            UserRole::Buyer => Inertia::render('dashboard/Buyer', $dashboards->forBuyer($user)),
            UserRole::Admin => Inertia::render('dashboard/Admin', $dashboards->forAdmin()),
        };
    }
}
