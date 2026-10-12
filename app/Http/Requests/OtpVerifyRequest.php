<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex'    => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit.',
            'otp.digits'   => 'Kode OTP harus berupa 6 digit angka.',
        ];
    }
}
