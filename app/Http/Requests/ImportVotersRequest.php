<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportVotersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'max:20480'],
            'pin_mode' => ['nullable', 'string', 'max:50'],
            'default_pin' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Pilih file Excel (.xlsx) atau CSV (.csv) data mahasiswa kampus terlebih dahulu.',
            'excel_file.max' => 'Ukuran file maksimal 20MB.',
        ];
    }
}
