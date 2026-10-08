<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SengketaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nim'            => ['required', 'string', 'regex:/^[0-9]{9,15}$/'],
            'tanggal_lahir'  => ['required', 'date'],
            'alasan'         => ['required', 'string', 'min:20', 'max:1000'],
            'pelapor_kontak' => ['required', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan.min' => 'Alasan pelaporan minimal 20 karakter.',
        ];
    }
}
