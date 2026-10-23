<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

/**
 * Reviews feed the reliability score: only the buyer of a delivered order
 * writes one. Administrators are allowed everything (Gate::before, moderation).
 */
class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return true;
    }

    /**
     * One review per delivered order, by its buyer.
     */
    public function create(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id
            && $order->status === OrderStatus::Delivered
            && ! $order->review()->exists();
    }

    public function update(User $user, Review $review): bool
    {
        return $review->order->buyer_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return false;
    }
}
