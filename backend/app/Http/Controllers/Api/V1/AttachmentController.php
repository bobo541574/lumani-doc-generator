<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_id' => ['required', 'uuid', 'exists:documents,id'],
            'file' => ['required', 'file'],
            'type' => ['sometimes', 'string', 'in:file,image,video,audio'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');

        $attachment = Attachment::create([
            'document_id' => $validated['document_id'],
            'file_name' => $file->getClientOriginalName(),
            'file_key' => $file->store('attachments'),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => $validated['type'] ?? 'file',
            'alt_text' => $validated['alt_text'] ?? null,
            'uploaded_by' => $request->user()?->id,
        ]);

        return response()->json(['data' => $attachment], 201);
    }

    public function destroy(Attachment $attachment)
    {
        $attachment->delete();

        return response()->json(null, 204);
    }
}
