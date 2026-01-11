<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportedLanguage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
