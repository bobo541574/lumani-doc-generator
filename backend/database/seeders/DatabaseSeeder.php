<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::factory()->create([
            'name' => 'Lumani',
            'slug' => 'lumani',
            'domain' => 'lumani.localhost',
            'subscription_tier' => 'enterprise',
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
            'email' => 'admin@lumani.local',
            'name' => 'Lumani Admin',
            'role' => 'admin',
        ]);

        $projects = Project::factory()
            ->count(5)
            ->create([
                'organization_id' => $org->id,
                'created_by' => $user->id,
            ]);

        foreach ($projects as $project) {
            Document::factory()
                ->count(10)
                ->create([
                    'project_id' => $project->id,
                    'created_by' => $user->id,
                ]);
        }
    }
}
