<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ProjectResource::collection(
            Project::query()
                ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
                ->paginate($request->integer('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string'],
        ]);

        $project = Project::create([
            ...$validated,
            'organization_id' => $request->user()?->organization_id,
            'created_by' => $request->user()?->id,
        ]);

        return ProjectResource::make($project);
    }

    public function show(Project $project)
    {
        return ProjectResource::make($project);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['sometimes', 'in:private,public'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $project->update($validated);

        return ProjectResource::make($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
