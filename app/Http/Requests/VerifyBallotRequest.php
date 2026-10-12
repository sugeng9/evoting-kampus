<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyBallotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'verification_code' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'verification_code.required' => 'Kode verifikasi suara wajib diisi.',
            'verification_code.max' => 'Kode verifikasi maksimal 50 karakter.',
        ];
    }
}
