<?php

namespace App\Services\Transcription;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class TranscriptionService
{
    public function __construct(
        private GroqWhisperService $groq,
        private OpenAIWhisperService $openai,
        private DeepgramService $deepgram,
    ) {}

    public function transcribe(
        UploadedFile $audio,
        User $user,
        ?string $provider = null,
        ?string $language = null
    ): array {
        $provider = $provider ?? $this->getDefaultProvider($user);
        $language = $language ?? ($user->auto_detect_language ? null : $user->preferred_language);

        $service = $this->getProvider($provider);

        $dictionary = $user->dictionaryWords()->pluck('word')->toArray();

        // Add team dictionary if user has a team
        if ($user->current_team_id) {
            $teamDictionary = $user->currentTeam?->sharedDictionary()->pluck('word')->toArray() ?? [];
            $dictionary = array_merge($dictionary, $teamDictionary);
        }

        $result = $service->transcribe($audio, $language, $dictionary);

        return [
            'text' => $result['text'],
            'duration' => $result['duration'],
            'language' => $result['language'],
            'provider' => $provider,
        ];
    }

    private function getProvider(string $provider): TranscriptionProviderInterface
    {
        return match ($provider) {
            'groq' => $this->groq,
            'openai' => $this->openai,
            'deepgram' => $this->deepgram,
            default => $this->groq,
        };
    }

    private function getDefaultProvider(User $user): string
    {
        if ($user->plan === 'free') {
            return 'groq';
        }

        return config('services.default_transcription_provider', 'groq');
    }
}
