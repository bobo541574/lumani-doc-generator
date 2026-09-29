<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:documents,id'],
            'title' => ['required', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:500', 'regex:/^[a-z0-9-]+$/'],
            'content' => ['nullable', 'string'],
            'content_type' => ['sometimes', 'string', 'in:mdx,md,html,plain'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'visibility' => ['sometimes', 'in:private,public'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
