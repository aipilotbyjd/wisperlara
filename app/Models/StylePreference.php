<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StylePreference extends Model
{
    protected $fillable = [
        'user_id',
        'app_identifier',
        'app_name',
        'style',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
