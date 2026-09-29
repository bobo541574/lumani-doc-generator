<?php

use App\Http\Middleware\RateLimitMiddleware;
use App\Models\Document;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(RateLimitMiddleware::class);
});

test('dashboard stats returns tenant-scoped real counts', function () {
    $org = Organization::factory()->create(['slug' => 'stats-org', 'is_active' => true]);

    $user = User::factory()->create([
        'organization_id' => $org->id,
        'role' => 'admin',
    ]);

    $project = Project::factory()->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
    ]);

    Document::factory()->count(3)->create([
        'project_id' => $project->id,
        'created_by' => $user->id,
    ]);

    Project::factory()->count(2)->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
    ]);

    $this->getJson('/api/v1/dashboard/stats', ['X-Tenant-ID' => $org->slug])
        ->assertOk()
        ->assertJsonPath('projects', 3)
        ->assertJsonPath('documents', 3)
        ->assertJsonStructure(['projects', 'documents', 'recentActivity']);
});

test('dashboard stats is scoped per tenant (no cross-tenant leakage)', function () {
    $orgA = Organization::factory()->create(['slug' => 'tenant-a', 'is_active' => true]);
    $orgB = Organization::factory()->create(['slug' => 'tenant-b', 'is_active' => true]);

    Project::factory()->count(4)->sequence(
        ['slug' => 'a-1'],
        ['slug' => 'a-2'],
        ['slug' => 'a-3'],
        ['slug' => 'a-4'],
    )->create(['organization_id' => $orgA->id]);

    Project::factory()->create([
        'organization_id' => $orgB->id,
        'slug' => 'b-1',
    ]);

    $this->getJson('/api/v1/dashboard/stats', ['X-Tenant-ID' => $orgB->slug])
        ->assertOk()
        ->assertJsonPath('projects', 1);
});