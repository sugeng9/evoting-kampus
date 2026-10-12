<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UbahFaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'fase'                => ['required', 'in:pendaftaran,sengketa,aktif,voting'],
            'pendaftaran_terbuka' => ['nullable'],
            'catatan'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'fase.required' => 'Fase sistem wajib dipilih.',
            'fase.in' => 'Pilihan fase sistem tidak valid.',
        ];
    }
}
