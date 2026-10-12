<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionEndTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'end_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.required' => 'Batas waktu pemilihan wajib diisi.',
            'end_at.date' => 'Format batas waktu pemilihan tidak valid.',
        ];
    }
}
