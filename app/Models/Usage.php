<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usage extends Model
{
    protected $table = 'usage';

    protected $fillable = [
        'user_id',
        'minutes_used',
        'transcription_count',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'minutes_used' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
