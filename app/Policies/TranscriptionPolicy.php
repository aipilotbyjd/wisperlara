<?php

namespace App\Policies;

use App\Models\Transcription;
use App\Models\User;

class TranscriptionPolicy
{
    public function view(User $user, Transcription $transcription): bool
    {
        return $user->id === $transcription->user_id;
    }

    public function delete(User $user, Transcription $transcription): bool
    {
        return $user->id === $transcription->user_id;
    }
}
