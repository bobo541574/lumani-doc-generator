<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use Symfony\Component\Yaml\Yaml;

class ExportService
{
    public function toJson(Document $document, bool $includeVersions = false): string
    {
        return json_encode($this->buildPayload($document, $includeVersions), JSON_PRETTY_PRINT);
    }

    public function toMarkdown(Document $document): string
    {
        $content = $document->content ?? '';

        $header = <<<MD
---
title: "{$document->title}"
slug: "{$document->slug}"
version: "{$document->version}"
status: "{$document->status}"
---

MD;

        return rtrim($header . "\n\n" . $content) . "\n";
    }

    public function toYaml(Document $document): string
    {
        return Yaml::dump($this->buildPayload($document));
    }

    private function buildPayload(Document $document, bool $includeVersions = false): array
    {
        $data = [
            'id' => $document->id,
            'title' => $document->title,
            'slug' => $document->slug,
            'content' => $document->content,
            'content_type' => $document->content_type,
            'version' => $document->version,
            'status' => $document->status,
            'metadata' => $document->metadata,
            'created_at' => $document->created_at,
            'updated_at' => $document->updated_at,
        ];

        if ($includeVersions) {
            $data['versions'] = $document->versions()->get()->toArray();
        }

        return $data;
    }
}
