<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return DocumentResource::collection(
            Document::query()
                ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->input('project_id')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
                ->paginate($request->integer('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:documents,id'],
            'title' => ['required', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:500', 'regex:/^[a-z0-9-]+$/'],
            'content' => ['nullable', 'string'],
            'content_type' => ['sometimes', 'string', 'in:mdx,md,html,plain'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'visibility' => ['sometimes', 'in:private,public'],
            'metadata' => ['sometimes', 'array'],
        ]);

        $document = Document::create($validated);

        return DocumentResource::make($document);
    }

    public function show(Document $document)
    {
        return DocumentResource::make($document);
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:500'],
            'content' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'visibility' => ['sometimes', 'in:private,public'],
            'is_published' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'array'],
        ]);

        $document->update($validated);

        return DocumentResource::make($document->fresh());
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(null, 204);
    }
}
