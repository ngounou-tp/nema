<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

/**
 * An order is seen by its buyer and by the seller side (producer and their
 * cooperative). Administrators are allowed everything (Gate::before).
 */
class OrderPolicy
{
    /**
     * Each role sees a list filtered to its own orders.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id || $user->actsForProducer($order->product->producer);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Buyer);
    }

    /**
     * The seller confirms and delivers.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->actsForProducer($order->product->producer);
    }

    /**
     * Orders are kept for the reliability score: only an administrator removes one.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}
