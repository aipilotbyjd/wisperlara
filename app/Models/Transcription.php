<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transcription extends Model
{
    protected $fillable = [
        'user_id',
        'original_text',
        'polished_text',
        'app_context',
        'style',
        'language',
        'duration_seconds',
        'word_count',
        'transcription_provider',
        'polishing_provider',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
