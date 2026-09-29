<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\IncomingDataQueue;
use App\Jobs\ProcessAttachmentJob;
use Illuminate\Support\Str;

class DocumentService
{
    public function createFromPayload(IncomingDataQueue $queue): Document
    {
        $payload = $queue->payload;
        $project = $queue->project;

        $document = Document::create([
            'project_id' => $queue->project_id,
            'title' => $payload['title'] ?? Str::title(Str::slug($payload['slug'] ?? uniqid())),
            'slug' => $payload['slug'] ?? (string) Str::uuid(),
            'content' => $payload['content'] ?? null,
            'metadata' => $payload['metadata'] ?? [],
            'content_type' => $payload['content_type'] ?? 'mdx',
            'status' => $payload['status'] ?? 'draft',
            'visibility' => $payload['visibility'] ?? $project->visibility ?? 'private',
        ]);

        if (!empty($payload['attachments'])) {
            foreach ($payload['attachments'] as $attachment) {
                ProcessAttachmentJob::dispatch(
                    \App\Models\Attachment::firstOrCreate(
                        ['document_id' => $document->id, 'file_key' => $attachment['file_key'] ?? Str::uuid()],
                        $attachment
                    )
                );
            }
        }

        return $document;
    }

    public function updateFromPayload(IncomingDataQueue $queue): void
    {
        $payload = $queue->payload;

        $document = Document::where('project_id', $queue->project_id)
            ->where('slug', $payload['slug'])
            ->first();

        if (!$document) {
            $this->createFromPayload($queue);
            return;
        }

        $content = $payload['content'] ?? $document->content;

        DocumentVersion::create([
            'document_id' => $document->id,
            'version' => $this->nextVersion($document->version),
            'content' => $content,
            'created_by' => null,
        ]);

        $document->update([
            'content' => $content,
            'metadata' => array_merge($document->metadata ?? [], $payload['metadata'] ?? []),
            'status' => $payload['status'] ?? $document->status,
            'visibility' => $payload['visibility'] ?? $document->visibility,
        ]);
    }

    public function deleteFromPayload(IncomingDataQueue $queue): void
    {
        $payload = $queue->payload;

        Document::where('project_id', $queue->project_id)
            ->where('slug', $payload['slug'])
            ->delete();
    }

    private function nextVersion(string $current): string
    {
        $parts = explode('.', $current);
        $parts[2] = ((int) ($parts[2] ?? 0)) + 1;
        return implode('.', $parts);
    }
}
