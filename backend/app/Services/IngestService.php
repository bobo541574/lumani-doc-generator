<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IncomingDataQueue;
use App\Models\Project;
use Illuminate\Support\Facades\Redis;

class IngestService
{
    public function initiate(Project $project, array $payload, string $syncId, array $attributes = []): IncomingDataQueue
    {
        $lockKey = "ingest:init:{$project->id}:{$syncId}";

        $locked = Redis::set($lockKey, true, 'EX', 60, 'NX');

        if (!$locked) {
            $existing = IncomingDataQueue::where('project_id', $project->id)
                ->where('sync_id', $syncId)
                ->first();

            if ($existing) {
                return $existing;
            }

            throw new \RuntimeException('Duplicate or concurrent ingestion request');
        }

        try {
            return IncomingDataQueue::create(array_merge([
                'project_id' => $project->id,
                'sync_id' => $syncId,
                'payload' => $payload,
            ], $attributes));
        } finally {
            Redis::del($lockKey);
        }
    }
}