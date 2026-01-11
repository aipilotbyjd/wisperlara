<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trigger_phrase' => ['required', 'string', 'max:255'],
            'replacement_text' => ['required', 'string', 'max:5000'],
        ];
    }
}
