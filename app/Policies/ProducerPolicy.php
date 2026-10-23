<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Producer;
use App\Models\User;

/**
 * A cooperative only manages its own members. Administrators are allowed
 * everything (Gate::before in AppServiceProvider).
 */
class ProducerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Producer $producer): bool
    {
        return true;
    }

    /**
     * Cooperatives register their members.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Cooperative);
    }

    public function update(User $user, Producer $producer): bool
    {
        return $user->actsForProducer($producer);
    }

    /**
     * Removing a member is the cooperative's decision.
     */
    public function delete(User $user, Producer $producer): bool
    {
        return $user->managesProducer($producer);
    }
}
