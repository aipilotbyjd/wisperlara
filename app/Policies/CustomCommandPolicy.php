<?php

namespace App\Policies;

use App\Models\CustomCommand;
use App\Models\User;

class CustomCommandPolicy
{
    public function view(User $user, CustomCommand $command): bool
    {
        return $user->id === $command->user_id;
    }

    public function update(User $user, CustomCommand $command): bool
    {
        return $user->id === $command->user_id;
    }

    public function delete(User $user, CustomCommand $command): bool
    {
        return $user->id === $command->user_id;
    }
}
