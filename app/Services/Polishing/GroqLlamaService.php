<?php

namespace App\Services\Polishing;

use Illuminate\Support\Facades\Http;

class GroqLlamaService implements PolishingProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
    }

    public function polish(string $text, string $prompt): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0.3,
                'max_tokens' => 2048,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Groq polishing failed: ' . $response->body());
        }

        $data = $response->json();
        $polishedText = $data['choices'][0]['message']['content'] ?? $text;

        return [
            'text' => trim($polishedText),
        ];
    }

    public function getProviderName(): string
    {
        return 'groq';
    }
}
