<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('voter')->check();
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:election_categories,id'],
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'confirmation_pin' => ['required', 'string', 'max:64'],
            'voting_token' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori pemilihan tidak valid.',
            'category_id.exists' => 'Kategori pemilihan tidak ditemukan.',
            'candidate_id.required' => 'Silakan pilih salah satu kandidat terlebih dahulu.',
            'candidate_id.exists' => 'Kandidat yang dipilih tidak valid.',
            'confirmation_pin.required' => 'Masukkan PIN Anda sebagai konfirmasi akhir.',
        ];
    }
}
