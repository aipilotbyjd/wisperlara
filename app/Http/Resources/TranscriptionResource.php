<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_text' => $this->original_text,
            'polished_text' => $this->polished_text,
            'app_context' => $this->app_context,
            'style' => $this->style,
            'language' => $this->language,
            'duration_seconds' => $this->duration_seconds,
            'word_count' => $this->word_count,
            'transcription_provider' => $this->transcription_provider,
            'polishing_provider' => $this->polishing_provider,
            'created_at' => $this->created_at,
        ];
    }
}
