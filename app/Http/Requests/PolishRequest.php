<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PolishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:50000'],
            'style' => ['nullable', 'string', 'in:formal,casual,extremely_casual'],
            'context' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'in:groq,gemini,openai'],
        ];
    }
}
