<?php

namespace App\Services\Polishing;

use Illuminate\Support\Facades\Http;

class OpenAIService implements PolishingProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? '';
    }

    public function polish(string $text, string $prompt): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0.3,
                'max_tokens' => 2048,
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI polishing failed: ' . $response->body());
        }

        $data = $response->json();
        $polishedText = $data['choices'][0]['message']['content'] ?? $text;

        return [
            'text' => trim($polishedText),
        ];
    }

    public function getProviderName(): string
    {
        return 'openai';
    }
}
