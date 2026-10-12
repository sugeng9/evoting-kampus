<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status_override' => ['required', 'in:AUTO,FORCE_OPEN,FORCE_CLOSED'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.after_or_equal' => 'Waktu selesai harus sama dengan atau setelah waktu mulai.',
            'status_override.required' => 'Pilihan mode status wajib ditentukan.',
            'status_override.in' => 'Pilihan mode status tidak valid.',
        ];
    }
}
