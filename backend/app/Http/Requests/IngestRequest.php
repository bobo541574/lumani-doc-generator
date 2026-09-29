<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'action' => ['sometimes', 'in:create,update,delete'],
            'source' => ['sometimes', 'string', 'max:255'],
            'target' => ['sometimes', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
