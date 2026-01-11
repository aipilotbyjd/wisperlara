<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trigger_phrase' => ['required', 'string', 'max:100'],
            'expansion_text' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:50'],
        ];
    }
}
