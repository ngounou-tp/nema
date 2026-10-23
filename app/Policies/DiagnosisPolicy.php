<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Diagnosis;
use App\Models\User;

/**
 * A diagnosis (and its photo) belongs to whoever took it. Administrators are
 * allowed everything (Gate::before).
 */
class DiagnosisPolicy
{
    /**
     * Lists are filtered to the user's own diagnoses.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Diagnosis $diagnosis): bool
    {
        return $diagnosis->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Producer) || $user->hasRole(UserRole::Cooperative);
    }

    public function update(User $user, Diagnosis $diagnosis): bool
    {
        return false;
    }

    public function delete(User $user, Diagnosis $diagnosis): bool
    {
        return $diagnosis->user_id === $user->id;
    }
}
