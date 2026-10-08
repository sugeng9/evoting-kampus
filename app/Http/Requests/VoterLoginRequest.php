<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoterLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'regex:/^[0-9]{9,15}$/'],
            'password' => ['required', 'string'],
        ];
    }
}
