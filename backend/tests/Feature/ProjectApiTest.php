<?php

use App\Http\Middleware\RateLimitMiddleware;
use App\Models\Organization;
use App\Models\Project;

beforeEach(function () {
    $this->withoutMiddleware(RateLimitMiddleware::class);
});

test('project API is protected by the tenant middleware', function () {
    $this->getJson('/api/v1/projects')
        ->assertStatus(400);
});

test('project index returns projects for the current tenant', function () {
    $org = Organization::factory()->create(['slug' => 'acme', 'is_active' => true]);
    Project::factory()->create(['organization_id' => $org->id, 'slug' => 'guide']);

    $this->getJson('/api/v1/projects', ['X-Tenant-ID' => $org->slug])
        ->assertOk()
        ->assertJsonPath('data.0.slug', 'guide')
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'visibility']]]);
});

test('project API returns 201 with the resource shape when creating', function () {
    $org = Organization::factory()->create(['slug' => 'acme', 'is_active' => true]);
    $user = \App\Models\User::factory()->create([
        'organization_id' => $org->id,
        'role' => 'admin',
    ]);

    $this->actingAs($user)
        ->postJson('/api/v1/projects', [
            'name' => 'New Guide',
            'slug' => 'new-guide',
            'description' => 'A guide',
        ], ['X-Tenant-ID' => $org->slug])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'slug', 'visibility']]);

    expect(Project::where('slug', 'new-guide')->first())
        ->not->toBeNull()
        ->and(Project::where('slug', 'new-guide')->value('organization_id'))->toBe($org->id);
});