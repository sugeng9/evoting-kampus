<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkGenerateTokensRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'mode' => ['nullable', 'string', 'in:only_untokened,regenerate_all'],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.in' => 'Mode penerbitan token tidak valid.',
        ];
    }
}
