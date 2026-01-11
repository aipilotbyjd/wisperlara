<?php

namespace App\Services\Transcription;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class DeepgramService implements TranscriptionProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.deepgram.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.deepgram.api_key') ?? '';
    }

    public function transcribe(UploadedFile $audio, ?string $language = null, array $dictionary = []): array
    {
        $queryParams = [
            'model' => 'nova-2',
            'smart_format' => 'true',
            'punctuate' => 'true',
        ];

        if ($language) {
            $queryParams['language'] = $language;
        } else {
            $queryParams['detect_language'] = 'true';
        }

        if (!empty($dictionary)) {
            $queryParams['keywords'] = implode(',', array_slice($dictionary, 0, 50));
        }

        if (empty($this->apiKey)) {
            throw new \Exception('Deepgram API key not configured');
        }

        $response = Http::withToken($this->apiKey)
            ->withBody(file_get_contents($audio->path()), $audio->getMimeType())
            ->post("{$this->baseUrl}/listen?" . http_build_query($queryParams));

        if (!$response->successful()) {
            throw new \Exception('Deepgram transcription failed: ' . $response->body());
        }

        $data = $response->json();
        $result = $data['results']['channels'][0]['alternatives'][0] ?? [];

        return [
            'text' => $result['transcript'] ?? '',
            'duration' => $data['metadata']['duration'] ?? 0,
            'language' => $data['results']['channels'][0]['detected_language'] ?? $language ?? 'en',
        ];
    }

    public function getProviderName(): string
    {
        return 'deepgram';
    }
}
