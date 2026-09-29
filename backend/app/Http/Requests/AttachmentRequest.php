<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'uuid', 'exists:documents,id'],
            'file' => ['required', 'file', 'max:51200'],
            'type' => ['sometimes', 'string', 'in:file,image,video,audio'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ];
    }
}
