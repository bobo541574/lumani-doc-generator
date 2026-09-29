<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version' => $this->version,
            'change_summary' => $this->change_summary,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
