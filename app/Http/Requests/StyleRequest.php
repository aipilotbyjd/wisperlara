<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StyleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_identifier' => ['required', 'string', 'max:100'],
            'app_name' => ['required', 'string', 'max:100'],
            'style' => ['required', 'string', 'in:formal,casual,extremely_casual'],
        ];
    }
}
