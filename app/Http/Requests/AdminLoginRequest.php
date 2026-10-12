<?php

namespace App\Http\Requests;

use App\Rules\TurnstileRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:128'],
            'cf-turnstile-response' => [new TurnstileRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username panitia wajib diisi.',
            'password.required' => 'Password panitia wajib diisi.',
        ];
    }
}
