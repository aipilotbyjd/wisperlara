<?php

namespace App\Providers;

use App\Models\CustomCommand;
use App\Models\DictionaryWord;
use App\Models\Snippet;
use App\Models\Transcription;
use App\Policies\CustomCommandPolicy;
use App\Policies\DictionaryWordPolicy;
use App\Policies\SnippetPolicy;
use App\Policies\TranscriptionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        DictionaryWord::class => DictionaryWordPolicy::class,
        CustomCommand::class => CustomCommandPolicy::class,
        Snippet::class => SnippetPolicy::class,
        Transcription::class => TranscriptionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
