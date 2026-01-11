<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DictionaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'word' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'pronunciation' => ['nullable', 'string', 'max:255'],
        ];
    }
}
