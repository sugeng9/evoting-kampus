<?php

namespace App\Http\Requests;

use App\Rules\TurnstileRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email:rfc', 'max:200'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
                'confirmed'
            ],
            'kode_pendaftaran' => ['nullable', 'string', 'max:64'],
            'website_trap' => ['nullable', 'string', 'max:100'],
            'form_time' => ['nullable', 'string'],
            'cf-turnstile-response' => [new TurnstileRule()],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 1. Anti-Bot Honeypot
            if ($this->filled('website_trap')) {
                $validator->errors()->add('nim', 'Permintaan Anda terdeteksi tidak valid.');
                return;
            }

            // 2. Anti-Disposable Email
            if ($this->filled('email')) {
                $email = strtolower(trim((string) $this->input('email')));
                $parts = explode('@', $email);
                $domain = end($parts);

                $disposableDomains = [
                    'tempmail.com', '10minutemail.com', 'mailinator.com', 'guerrillamail.com',
                    'sharklasers.com', 'dispostable.com', 'yopmail.com', 'trashmail.com',
                    'getairmail.com', 'temp-mail.org', 'nada.ltd', 'mohmal.com', 'crazymailing.com',
                    'fakemailgenerator.com', 'generator.email', 'emailondeck.com', 'dropmail.me',
                    'tempmailo.com', 'inboxkitten.com', 'tmpmail.org', 'disposablemail.com'
                ];

                if (in_array($domain, $disposableDomains)) {
                    $validator->errors()->add('email', 'Penggunaan alamat email sementara / palsu (disposable email) dilarang. Harap gunakan email pribadi Anda.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit (contoh: 120140001).',
            'tanggal_lahir.required' => 'Tanggal lahir mahasiswa wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'email.required' => 'Email pribadi aktif wajib diisi untuk menerima OTP.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib dibuat.',
            'password.min' => 'Kata sandi minimal 8 karakter dan wajib memuat kombinasi huruf besar, huruf kecil, serta angka.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok. Harap masukkan kata sandi yang sama persis.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nim' => 'NIM',
            'tanggal_lahir' => 'Tanggal Lahir',
            'email' => 'Email',
            'no_hp' => 'Nomor HP',
            'password' => 'Password',
            'kode_pendaftaran' => 'Kode Pendaftaran',
        ];
    }
}
