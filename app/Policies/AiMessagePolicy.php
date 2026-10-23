<?php

namespace App\Policies;

use App\Models\AiMessage;
use App\Models\User;

/**
 * Messages follow their conversation; they are written by the application,
 * never edited. Administrators are allowed everything (Gate::before).
 */
class AiMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AiMessage $aiMessage): bool
    {
        return $aiMessage->conversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AiMessage $aiMessage): bool
    {
        return false;
    }

    public function delete(User $user, AiMessage $aiMessage): bool
    {
        return false;
    }
}
