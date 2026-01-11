<?php

namespace App\Services\Polishing;

interface PolishingProviderInterface
{
    public function polish(string $text, string $prompt): array;
    
    public function getProviderName(): string;
}
