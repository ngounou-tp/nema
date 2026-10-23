<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Lot;
use App\Models\User;

/**
 * Lots are public (QR verification page); only the producer and their
 * cooperative write them. Administrators are allowed everything (Gate::before).
 */
class LotPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, Lot $lot): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Producer) || $user->hasRole(UserRole::Cooperative);
    }

    public function update(User $user, Lot $lot): bool
    {
        return $user->actsForProducer($lot->product->producer);
    }

    public function delete(User $user, Lot $lot): bool
    {
        return $user->actsForProducer($lot->product->producer);
    }
}
