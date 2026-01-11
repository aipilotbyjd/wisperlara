<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Billable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'plan',
        'preferred_language',
        'auto_detect_language',
        'current_team_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'password' => 'hashed',
            'auto_detect_language' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function usage(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    public function dictionaryWords(): HasMany
    {
        return $this->hasMany(DictionaryWord::class);
    }

    public function customCommands(): HasMany
    {
        return $this->hasMany(CustomCommand::class);
    }

    public function snippets(): HasMany
    {
        return $this->hasMany(Snippet::class);
    }

    public function stylePreferences(): HasMany
    {
        return $this->hasMany(StylePreference::class);
    }

    public function transcriptions(): HasMany
    {
        return $this->hasMany(Transcription::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }
}
