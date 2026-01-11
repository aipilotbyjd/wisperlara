<?php

namespace App\Services\Transcription;

use App\Exceptions\TranscriptionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqWhisperService implements TranscriptionProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key') ?? '';
    }

    public function transcribe(UploadedFile $audio, ?string $language = null, array $dictionary = []): array
    {
        if (empty($this->apiKey)) {
            throw new TranscriptionException('Groq API key not configured', 'groq');
        }

        try {
            $response = Http::timeout(120)
                ->withToken($this->apiKey)
                ->attach('file', file_get_contents($audio->path()), $audio->getClientOriginalName())
                ->post("{$this->baseUrl}/audio/transcriptions", array_filter([
                    'model' => 'whisper-large-v3-turbo',
                    'language' => $language,
                    'response_format' => 'verbose_json',
                    'prompt' => !empty($dictionary) ? implode(', ', array_slice($dictionary, 0, 50)) : null,
                ]));

            if (!$response->successful()) {
                Log::error('Groq transcription failed', ['response' => $response->body()]);
                throw new TranscriptionException(
                    'Transcription failed. Please try again.',
                    'groq',
                    ['status' => $response->status()]
                );
            }

            $data = $response->json();

            return [
                'text' => $data['text'] ?? '',
                'duration' => $data['duration'] ?? 0,
                'language' => $data['language'] ?? $language ?? 'en',
            ];
        } catch (TranscriptionException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Groq transcription error', ['error' => $e->getMessage()]);
            throw new TranscriptionException('Transcription service unavailable', 'groq');
        }
    }

    public function getProviderName(): string
    {
        return 'groq';
    }
}
