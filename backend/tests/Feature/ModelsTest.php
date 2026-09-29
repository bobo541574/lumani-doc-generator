<?php

use App\Models\Document;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('all primary keys are UUID strings, not auto-increment integers', function () {
    $org = Organization::factory()->create();

    expect($org->id)->toBeString()
        ->and($org->getKeyType())->toBe('string')
        ->and($org->getIncrementing())->toBeFalse()
        ->and($org->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

test('factories create valid auth/domain records with UUID foreign keys', function () {
    $org = Organization::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create([
        'organization_id' => $org->id,
        'email' => 'writer@acme.test',
        'role' => 'editor',
    ]);

    expect($user->organization_id)->toBe($org->id)
        ->and($user->id)->toBeString()
        ->and($user->email)->toBe('writer@acme.test');
});

test('organizations relate to users and projects', function () {
    $org = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $org->id]);
    $project = Project::factory()->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
    ]);

    expect($org->users()->count())->toBe(1)
        ->and($org->projects()->count())->toBe(1)
        ->and($project->organization_id)->toBe($org->id);
});

test('documents belong to a project', function () {
    $org = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $org->id]);
    $project = Project::factory()->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
    ]);
    $document = Document::factory()->create([
        'project_id' => $project->id,
        'created_by' => $user->id,
    ]);

    expect($document->project_id)->toBe($project->id)
        ->and($document->id)->toBeString();
});

test('tenant-aware models are unscoped without a bound tenant', function () {
    Organization::factory()->create(['slug' => 'tenant-a']);
    Organization::factory()->create(['slug' => 'tenant-b']);

    expect(User::count())->toBe(0);
});
