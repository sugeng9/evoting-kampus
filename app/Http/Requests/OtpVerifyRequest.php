<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string'],
            'otp'  => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.digits' => 'Kode OTP harus berupa 6 digit angka.',
        ];
    }
}
