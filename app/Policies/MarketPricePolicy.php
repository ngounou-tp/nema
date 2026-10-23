<?php

namespace App\Policies;

use App\Models\MarketPrice;
use App\Models\User;

/**
 * Market prices are read by everyone and maintained by administrators (Gate::before).
 */
class MarketPricePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MarketPrice $marketPrice): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MarketPrice $marketPrice): bool
    {
        return false;
    }

    public function delete(User $user, MarketPrice $marketPrice): bool
    {
        return false;
    }
}
