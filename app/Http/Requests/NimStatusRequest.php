<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NimStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nim'           => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'tanggal_lahir' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex'    => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit (contoh: 120140001).',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date'     => 'Format tanggal lahir tidak valid.',
        ];
    }
}
