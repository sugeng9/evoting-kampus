<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SengketaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nim'            => ['required', 'string', 'regex:/^[0-9]{8,10}$/'],
            'tanggal_lahir'  => ['required', 'date'],
            'alasan'         => ['required', 'string', 'min:20', 'max:1000'],
            'pelapor_kontak' => ['required', 'string', 'max:200'],
            'bukti_ktm'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex'    => 'NIM hanya boleh berupa angka (integer) dengan panjang 8 hingga 10 digit (contoh: 120140001).',
            'alasan.min'   => 'Alasan pelaporan minimal 20 karakter.',
            'bukti_ktm.mimes' => 'Format berkas bukti KTM harus berupa gambar (JPG, JPEG, PNG) atau dokumen PDF.',
            'bukti_ktm.max'   => 'Ukuran berkas bukti KTM maksimal 3 MB.',
        ];
    }
}
