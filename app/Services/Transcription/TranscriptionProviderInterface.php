<?php

namespace App\Services\Transcription;

use Illuminate\Http\UploadedFile;

interface TranscriptionProviderInterface
{
    public function transcribe(UploadedFile $audio, ?string $language = null, array $dictionary = []): array;
    
    public function getProviderName(): string;
}
