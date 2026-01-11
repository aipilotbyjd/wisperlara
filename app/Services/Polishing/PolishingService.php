<?php

namespace App\Services\Polishing;

use App\Models\User;

class PolishingService
{
    private array $stylePrompts = [
        'formal' => 'Clean the text with full punctuation, proper capitalization, and professional grammar.',
        'casual' => 'Clean the text with basic punctuation, conversational tone. Less formal.',
        'extremely_casual' => 'Clean the text with minimal punctuation, lowercase, texting style.',
    ];

    public function __construct(
        private GroqLlamaService $groq,
        private GeminiService $gemini,
        private OpenAIService $openai,
    ) {}

    public function polish(
        string $text,
        User $user,
        string $style = 'casual',
        ?string $appContext = null,
        ?string $provider = null
    ): array {
        // Get app-specific style if available
        if ($appContext) {
            $stylePref = $user->stylePreferences()
                ->where('app_identifier', $appContext)
                ->first();

            if ($stylePref) {
                $style = $stylePref->style;
            }
        }

        $provider = $provider ?? $this->getDefaultProvider($user);

        $service = $this->getProvider($provider);

        // Get user's custom commands
        $commands = $user->customCommands()
            ->where('is_active', true)
            ->get();

        // Get user's snippets
        $snippets = $user->snippets()
            ->where('is_active', true)
            ->get();

        // Apply custom commands first
        $processedText = $this->applyCommands($text, $commands);
        
        // Apply snippets
        $processedText = $this->applySnippets($processedText, $snippets);

        // Polish with LLM
        $prompt = $this->buildPrompt($style);
        $result = $service->polish($processedText, $prompt);

        return [
            'text' => $result['text'],
            'style' => $style,
            'provider' => $provider,
        ];
    }

    private function applyCommands($text, $commands): string
    {
        foreach ($commands as $command) {
            $text = str_ireplace(
                $command->trigger_phrase,
                $command->replacement_text,
                $text
            );
        }
        return $text;
    }

    private function applySnippets($text, $snippets): string
    {
        foreach ($snippets as $snippet) {
            $text = str_ireplace(
                $snippet->trigger_phrase,
                $snippet->expansion_text,
                $text
            );
        }
        return $text;
    }

    private function buildPrompt(string $style): string
    {
        return "You are a voice transcription cleaner. Your ONLY job is to:
1. Remove filler words: um, uh, like, you know, basically, actually, so, well, I mean
2. Fix typos and transcription errors
3. Add punctuation where natural pauses occur
4. Capitalize properly

STRICT RULES:
- Keep the EXACT same sentence structure
- Do NOT reformat into lists or bullet points
- Do NOT rephrase or rewrite
- Do NOT add or remove information
- Output should read naturally as spoken text

Style: {$this->stylePrompts[$style]}

Output ONLY the cleaned text, nothing else:";
    }

    private function getProvider(string $provider): PolishingProviderInterface
    {
        return match ($provider) {
            'groq' => $this->groq,
            'gemini' => $this->gemini,
            'openai' => $this->openai,
            default => $this->gemini,
        };
    }

    private function getDefaultProvider(User $user): string
    {
        if ($user->plan === 'free') {
            return 'gemini';
        }

        return config('services.default_polishing_provider', 'gemini');
    }
}
