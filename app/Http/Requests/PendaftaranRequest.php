<?php

namespace App\Http\Requests;

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
            'nim' => ['required', 'string', 'min:4', 'max:25', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email:rfc', 'max:200'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'kode_pendaftaran' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.regex' => 'Format NIM hanya boleh berupa angka dan huruf.',
            'nim.min' => 'NIM minimal 4 karakter.',
            'tanggal_lahir.required' => 'Tanggal lahir mahasiswa wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'email.required' => 'Email pribadi aktif wajib diisi untuk menerima OTP.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib dibuat.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
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
