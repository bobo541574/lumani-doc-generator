<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'file_url' => $this->file_url,
            'thumbnail_url' => $this->thumbnail_url,
            'type' => $this->type,
            'alt_text' => $this->alt_text,
            'metadata' => $this->metadata,
            'processing_status' => $this->processing_status,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
        ];
    }
}
