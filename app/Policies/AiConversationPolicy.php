<?php

namespace App\Policies;

use App\Models\AiConversation;
use App\Models\User;

/**
 * Conversations with the assistant are private to their author. Administrators
 * are allowed everything (Gate::before, AI journal).
 */
class AiConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AiConversation $aiConversation): bool
    {
        return $aiConversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AiConversation $aiConversation): bool
    {
        return $aiConversation->user_id === $user->id;
    }

    public function delete(User $user, AiConversation $aiConversation): bool
    {
        return $aiConversation->user_id === $user->id;
    }
}
