<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'content_type' => $this->content_type,
            'version' => $this->version,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'order_index' => $this->order_index,
            'metadata' => $this->metadata,
            'ai_generated' => $this->ai_generated,
            'view_count' => $this->view_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'versions' => DocumentVersionResource::collection($this->whenLoaded('versions')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
