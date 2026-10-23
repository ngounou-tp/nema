<?php

namespace App\Policies;

use App\Models\KnowledgeChunk;
use App\Models\User;

/**
 * The assistant's knowledge base is maintained by administrators only (Gate::before).
 */
class KnowledgeChunkPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, KnowledgeChunk $knowledgeChunk): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, KnowledgeChunk $knowledgeChunk): bool
    {
        return false;
    }

    public function delete(User $user, KnowledgeChunk $knowledgeChunk): bool
    {
        return false;
    }
}
