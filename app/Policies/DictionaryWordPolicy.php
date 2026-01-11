<?php

namespace App\Policies;

use App\Models\DictionaryWord;
use App\Models\User;

class DictionaryWordPolicy
{
    public function view(User $user, DictionaryWord $word): bool
    {
        return $user->id === $word->user_id;
    }

    public function update(User $user, DictionaryWord $word): bool
    {
        return $user->id === $word->user_id;
    }

    public function delete(User $user, DictionaryWord $word): bool
    {
        return $user->id === $word->user_id;
    }
}
