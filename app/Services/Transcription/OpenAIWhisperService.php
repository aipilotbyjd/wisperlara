<?php

namespace App\Services\Transcription;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class OpenAIWhisperService implements TranscriptionProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? '';
    }

    public function transcribe(UploadedFile $audio, ?string $language = null, array $dictionary = []): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withToken($this->apiKey)
            ->attach('file', file_get_contents($audio->path()), $audio->getClientOriginalName())
            ->post("{$this->baseUrl}/audio/transcriptions", array_filter([
                'model' => 'whisper-1',
                'language' => $language,
                'response_format' => 'verbose_json',
                'prompt' => !empty($dictionary) ? implode(', ', array_slice($dictionary, 0, 50)) : null,
            ]));

        if (!$response->successful()) {
            throw new \Exception('OpenAI transcription failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'text' => $data['text'] ?? '',
            'duration' => $data['duration'] ?? 0,
            'language' => $data['language'] ?? $language ?? 'en',
        ];
    }

    public function getProviderName(): string
    {
        return 'openai';
    }
}
