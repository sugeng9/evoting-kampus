<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetDemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM demo wajib disertakan.',
            'nim.regex' => 'Format NIM harus berupa digit angka.',
        ];
    }
}
