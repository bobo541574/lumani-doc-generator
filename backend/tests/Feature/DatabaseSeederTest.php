<?php

use App\Models\Document;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('database seeder creates an org, admin user, projects and documents', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Organization::where('slug', 'lumani')->exists())->toBeTrue()
        ->and(User::where('email', 'admin@lumani.local')->exists())->toBeTrue()
        ->and(Project::count())->toBe(5)
        ->and(Document::count())->toBe(50);
});

test('seeded admin user belongs to the seeded organization', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@lumani.local')->first();
    $org = Organization::where('slug', 'lumani')->first();

    expect($admin->organization_id)->toBe($org->id)
        ->and($admin->role)->toBe('admin');
});
