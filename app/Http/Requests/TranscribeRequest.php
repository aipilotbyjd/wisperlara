<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:wav,mp3,webm,m4a,ogg,flac', 'max:25600'],
            'language' => ['nullable', 'string', 'max:10'],
            'provider' => ['nullable', 'string', 'in:groq,openai,deepgram'],
        ];
    }
}
