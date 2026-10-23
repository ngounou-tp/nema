<?php

namespace App\Policies;

use App\Models\SupplierScore;
use App\Models\User;

/**
 * Scores are computed by Laravel from orders and reviews, never edited by hand.
 * Administrators are allowed everything (Gate::before).
 */
class SupplierScorePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupplierScore $supplierScore): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SupplierScore $supplierScore): bool
    {
        return false;
    }

    public function delete(User $user, SupplierScore $supplierScore): bool
    {
        return false;
    }
}
