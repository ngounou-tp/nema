<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

/**
 * A producer only edits their own products; their cooperative may manage
 * them too. Administrators are allowed everything (Gate::before).
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return ($user->hasRole(UserRole::Producer) && $user->producer()->exists())
            || $user->hasRole(UserRole::Cooperative);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->actsForProducer($product->producer);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->actsForProducer($product->producer);
    }
}
