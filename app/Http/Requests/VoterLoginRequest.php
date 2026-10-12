<?php

namespace App\Http\Requests;

use App\Rules\TurnstileRule;
use Illuminate\Foundation\Http\FormRequest;

class VoterLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => [new TurnstileRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit (contoh: 120140001).',
            'password.required' => 'Password wajib diisi.',
    }
}
