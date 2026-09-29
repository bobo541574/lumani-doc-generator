<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookResource;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebhookController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return WebhookResource::collection(
            Webhook::query()
                ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->input('project_id')))
                ->paginate($request->integer('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'url', 'max:1000'],
            'events' => ['required', 'array'],
            'secret' => ['nullable', 'string', 'max:255'],
            'headers' => ['sometimes', 'array'],
        ]);

        $webhook = Webhook::create($validated);

        return response()->json(WebhookResource::make($webhook), 201);
    }

    public function show(Webhook $webhook)
    {
        return WebhookResource::make($webhook);
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'string', 'url', 'max:1000'],
            'events' => ['sometimes', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($validated);

        return WebhookResource::make($webhook->fresh());
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return response()->json(null, 204);
    }
}
