<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIngestJob;
use App\Models\IncomingDataQueue;
use App\Models\Project;
use App\Services\IngestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IngestController extends Controller
{
    public function __construct(private readonly IngestService $ingestService)
    {
    }

    public function ingest(Request $request, string $projectSlug): JsonResponse
    {
        $project = Project::where('slug', $projectSlug)
            ->where('organization_id', app('tenant')->id)
            ->where('is_active', true)
            ->firstOrFail();

        $syncId = $request->header('X-Sync-Id');

        if (!$syncId) {
            return response()->json([
                'error' => 'X-Sync-Id header is required for idempotency',
            ], 422);
        }

        $validated = $request->validate([
            'payload' => ['required', 'array'],
            'action' => ['sometimes', 'in:create,update,delete'],
            'source' => ['sometimes', 'string', 'max:255'],
            'target' => ['sometimes', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
        ]);

        try {
            $queue = $this->ingestService->initiate(
                $project,
                $validated['payload'],
                $syncId,
                [
                    'action' => $validated['action'] ?? 'create',
                    'status' => 'pending',
                    'max_retries' => 3,
                    'metadata' => [
                        'source' => $validated['source'] ?? null,
                        'target' => $validated['target'] ?? null,
                        'processing_time' => null,
                        'memory_usage' => null,
                        'steps_completed' => [],
                    ],
                ]
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 'Request is being processed or duplicate detected',
            ], 409);
        }

        if (!$queue->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Duplicate request already accepted',
                'queue_id' => $queue->id,
                'status' => $queue->status,
            ], 200);
        }

        ProcessIngestJob::dispatch($queue);

        return response()->json([
            'message' => 'Payload accepted for processing',
            'queue_id' => $queue->id,
            'status' => 'pending',
            'links' => [
                'status' => route('api.v1.ingest.status', ['queue_id' => $queue->id]),
            ],
        ], 202);
    }

    public function status(Request $request, string $queueId): JsonResponse
    {
        $projectId = $request->input('project_id');

        $query = IncomingDataQueue::where('id', $queueId);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $queue = $query->firstOrFail();

        return response()->json([
            'queue_id' => $queue->id,
            'status' => $queue->status,
            'progress' => $queue->progress,
            'error' => $queue->error_message,
            'processed_at' => $queue->processed_at,
        ]);
    }
}