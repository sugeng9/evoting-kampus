<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetVoterPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'new_password' => ['nullable', 'string', 'min:6', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
            'new_password.max' => 'Kata sandi baru maksimal 64 karakter.',
        ];
    }
}
