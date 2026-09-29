<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $tenant = app('tenant');

        if (!$tenant instanceof Organization) {
            abort(400, 'Tenant context required');
        }

        $projectIds = Project::query()
            ->where('organization_id', $tenant->id)
            ->pluck('id');

        $projectsCount = $projectIds->count();
        $documentsCount = Document::query()
            ->whereIn('project_id', $projectIds)
            ->count();

        $recentActivity = DB::table('activity_logs')
            ->where('organization_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'action', 'created_at']);

        return response()->json([
            'projects' => $projectsCount,
            'documents' => $documentsCount,
            'recentActivity' => $recentActivity->map(fn ($row) => [
                'id' => $row->id,
                'action' => $row->action,
                'created_at' => $row->created_at,
            ]),
        ]);
    }
}