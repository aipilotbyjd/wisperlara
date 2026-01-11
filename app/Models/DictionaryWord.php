<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictionaryWord extends Model
{
    protected $fillable = [
        'user_id',
        'word',
        'category',
        'pronunciation',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
