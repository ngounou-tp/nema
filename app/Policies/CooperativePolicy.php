<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Cooperative;
use App\Models\User;

/**
 * Administrators are allowed everything (Gate::before in AppServiceProvider).
 */
class CooperativePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Cooperative $cooperative): bool
    {
        return true;
    }

    /**
     * A cooperative account manages one cooperative.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Cooperative) && ! $user->managedCooperative()->exists();
    }

    public function update(User $user, Cooperative $cooperative): bool
    {
        return $cooperative->manager_id === $user->id;
    }

    public function delete(User $user, Cooperative $cooperative): bool
    {
        return $cooperative->manager_id === $user->id;
    }
}
