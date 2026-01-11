<?php

namespace App\Services\Polishing;

use Illuminate\Support\Facades\Http;

class GeminiService implements PolishingProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function polish(string $text, string $prompt): array
    {
        $response = Http::post("{$this->baseUrl}/models/gemini-1.5-flash:generateContent?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "{$prompt}\n\nText to clean:\n{$text}"]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 2048,
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini polishing failed: ' . $response->body());
        }

        $data = $response->json();
        $polishedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? $text;

        return [
            'text' => trim($polishedText),
        ];
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }
}
