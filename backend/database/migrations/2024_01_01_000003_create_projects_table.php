<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('organization_id');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->jsonb('config')->default(json_encode([
                'theme' => 'default',
                'enable_ai' => true,
                'enable_versioning' => true,
                'enable_comments' => false,
                'enable_webhooks' => true,
                'default_visibility' => 'private',
                'attachments' => [
                    'max_size' => 52428800,
                    'allowed_types' => ['image/*', 'application/pdf', 'video/*', 'audio/*'],
                ],
            ]));
            $table->string('visibility', 50)->default('private');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->unique(['organization_id', 'slug']);
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
