<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:DRAFT,OPEN,PAUSED,CLOSED'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status sesi pemilihan wajib dipilih.',
            'status.in' => 'Status sesi pemilihan yang dipilih tidak valid.',
        ];
    }
}
