<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('project_id');
            $table->uuid('parent_id')->nullable();
            $table->string('title', 500);
            $table->string('slug', 500);
            $table->text('content')->nullable();
            $table->string('content_type', 50)->default('mdx');
            $table->string('version', 20)->default('1.0.0');
            $table->string('status', 50)->default('draft');
            $table->string('visibility', 50)->default('private');
            $table->boolean('is_published')->default(false);
            $table->timestampTz('published_at')->nullable();
            $table->integer('order_index')->default(0);
            $table->jsonb('metadata')->default(json_encode([
                'tags' => [],
                'categories' => [],
                'authors' => [],
                'reviewers' => [],
                'last_reviewed_at' => null,
                'next_review_at' => null,
            ]));
            $table->boolean('ai_generated')->default(false);
            $table->jsonb('ai_metadata')->default(json_encode([
                'generated_by' => null,
                'model' => null,
                'prompt_version' => null,
                'confidence_score' => null,
            ]));
            $table->integer('view_count')->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->unique(['project_id', 'slug', 'version']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('visibility');
            $table->index('published_at');
            $table->index('created_at');
        });

        DB::statement('ALTER TABLE documents ADD COLUMN search_vector TSVECTOR');
        DB::statement('CREATE INDEX idx_documents_search_vector ON documents USING GIN(search_vector)');
        DB::statement('CREATE TRIGGER documents_search_vector_update BEFORE INSERT OR UPDATE ON documents FOR EACH ROW EXECUTE FUNCTION tsvector_update_trigger(search_vector, \'pg_catalog.english\', title, content)');

        // The self-referencing foreign key must be added after the table (and its
        // primary key) exists; PostgreSQL requires a unique constraint on the
        // referenced column before a foreign key can reference it.
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
