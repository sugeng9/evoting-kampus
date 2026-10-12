<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'election_category_id' => ['required', 'exists:election_categories,id'],
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates')->where(fn ($query) => 
                    $query->where('election_category_id', $this->election_category_id)
                ),
            ],
            'leader_name' => ['required', 'string', 'max:150'],
            'leader_nim' => ['nullable', 'string', 'max:20'],
            'vice_leader_name' => ['nullable', 'string', 'max:150'],
            'vice_leader_nim' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'vision' => ['required', 'string'],
            'mission' => ['required', 'string'],
            'programs_work' => ['nullable', 'string'],
            'is_active' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'election_category_id.required' => 'Kategori pemilihan wajib dipilih.',
            'election_category_id.exists' => 'Kategori pemilihan yang dipilih tidak valid.',
            'candidate_number.required' => 'Nomor urut paslon wajib diisi.',
            'candidate_number.unique' => 'Nomor urut ini sudah digunakan oleh kandidat lain pada kategori yang sama.',
            'leader_name.required' => 'Nama calon ketua wajib diisi.',
            'photo.image' => 'File foto harus berupa gambar (jpg, jpeg, png, webp).',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'vision.required' => 'Visi paslon wajib diisi.',
            'mission.required' => 'Misi paslon wajib diisi.',
        ];
    }
}
