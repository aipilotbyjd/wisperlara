<?php

namespace App\Policies;

use App\Models\Snippet;
use App\Models\User;

class SnippetPolicy
{
    public function view(User $user, Snippet $snippet): bool
    {
        return $user->id === $snippet->user_id;
    }

    public function update(User $user, Snippet $snippet): bool
    {
        return $user->id === $snippet->user_id;
    }

    public function delete(User $user, Snippet $snippet): bool
    {
        return $user->id === $snippet->user_id;
    }
}
