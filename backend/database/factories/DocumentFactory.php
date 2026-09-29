<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(4),
            'slug' => $this->faker->unique()->slug(3),
            'content' => $this->faker->paragraphs(5, true),
            'content_type' => 'mdx',
            'version' => '1.0.0',
            'status' => 'draft',
            'visibility' => 'private',
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
