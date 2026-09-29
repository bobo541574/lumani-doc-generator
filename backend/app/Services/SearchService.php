<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Str;

class SearchService
{
    public function search(string $query, array $filters = []): mixed
    {
        $client = app('meilisearch');

        $index = $client->index('documents');

        $options = [
            'filter' => $this->buildFilter($filters),
            'limit' => (int) ($filters['limit'] ?? 25),
            'offset' => (int) ($filters['offset'] ?? 0),
        ];

        return $index->search($query, $options);
    }

    public function rebuildIndex(iterable $documents): void
    {
        $client = app('meilisearch');
        $index = $client->index('documents');

        foreach ($documents as $document) {
            $this->indexDocument($index, $document);
        }
    }

    public function indexDocument($index, Document $document): void
    {
        $index->addDocuments([
            [
                'id' => $document->id,
                'project_id' => $document->project_id,
                'title' => $document->title,
                'content' => $document->content,
                'status' => $document->status,
                'visibility' => $document->visibility,
                'version' => $document->version,
            ],
        ]);
    }

    private function buildFilter(array $filters): ?array
    {
        $parts = [];

        if (!empty($filters['project_id'])) {
            $projectId = $filters['project_id'];

            if (Str::isUuid($projectId)) {
                $parts[] = 'project_id = ' . $this->quote($projectId);
            }
        }

        if (!empty($filters['status'])) {
            $status = strtolower((string) $filters['status']);

            if (in_array($status, ['draft', 'published', 'archived', 'unpublished'], true)) {
                $parts[] = 'status = ' . $this->quote($status);
            }
        }

        if (!empty($filters['visibility'])) {
            $visibility = strtolower((string) $filters['visibility']);

            if (in_array($visibility, ['private', 'public'], true)) {
                $parts[] = 'visibility = ' . $this->quote($visibility);
            }
        }

        return $parts ?: null;
    }

    private function quote(string $value): string
    {
        return "'" . addcslashes($value, "\\'\"") . "'";
    }
}
