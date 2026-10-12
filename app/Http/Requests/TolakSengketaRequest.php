<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TolakSengketaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'catatan_tolak' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_tolak.required' => 'Catatan alasan penolakan sengketa wajib diisi.',
            'catatan_tolak.min' => 'Catatan penolakan minimal 10 karakter agar informatif bagi pemilih.',
            'catatan_tolak.max' => 'Catatan penolakan maksimal 500 karakter.',
        ];
    }
}
