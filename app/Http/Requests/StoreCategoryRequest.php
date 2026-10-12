<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:election_categories,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'faculty_filter' => ['nullable', 'string', 'max:100'],
            'study_program_filter' => ['nullable', 'string', 'max:100'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status_override' => ['required', 'in:AUTO,FORCE_OPEN,FORCE_CLOSED'],
            'display_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode kategori wajib diisi.',
            'code.unique' => 'Kode kategori ini sudah digunakan oleh organisasi lain.',
            'code.alpha_dash' => 'Kode kategori hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'name.required' => 'Nama kategori pemilihan wajib diisi.',
            'end_at.after_or_equal' => 'Waktu selesai harus sama atau setelah waktu mulai.',
            'status_override.in' => 'Status override pemilihan tidak valid.',
        ];
    }
}
