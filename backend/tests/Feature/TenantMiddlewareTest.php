<?php

use App\Http\Middleware\RateLimitMiddleware;
use App\Models\Organization;

beforeEach(function () {
    $this->withoutMiddleware(RateLimitMiddleware::class);
});

test('tenant middleware rejects requests without a tenant identifier', function () {
    $this->getJson('/api/v1/dashboard/stats')
        ->assertStatus(400);
});

test('tenant middleware resolves an organization from the X-Tenant-ID header', function () {
    $org = Organization::factory()->create(['slug' => 'acme', 'is_active' => true]);

    $this->getJson('/api/v1/dashboard/stats', ['X-Tenant-ID' => $org->slug])
        ->assertOk()
        ->assertJson([
            'projects' => 0,
            'documents' => 0,
        ]);
});

test('tenant middleware rejects an inactive organization', function () {
    $org = Organization::factory()->create(['slug' => 'dormant', 'is_active' => false]);

    $this->getJson('/api/v1/dashboard/stats', ['X-Tenant-ID' => $org->slug])
        ->assertStatus(403);
});
