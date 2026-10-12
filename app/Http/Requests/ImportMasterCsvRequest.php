<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportMasterCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Pilih file CSV data master pemilih terlebih dahulu.',
            'csv_file.mimes' => 'Format file harus berupa CSV atau TXT.',
            'csv_file.max' => 'Ukuran file CSV maksimal 5MB.',
        ];
    }
}
