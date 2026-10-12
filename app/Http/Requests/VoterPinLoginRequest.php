<?php

namespace App\Http\Requests;

use App\Rules\TurnstileRule;
use Illuminate\Foundation\Http\FormRequest;

class VoterPinLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'pin' => ['required', 'string', 'min:4', 'max:64'],
            'cf-turnstile-response' => [new TurnstileRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit (contoh: 120140001).',
            'pin.required' => 'Kata sandi akun wajib diisi.',
            'pin.min' => 'Kata sandi minimal 4 karakter.',
        ];
    }
}
