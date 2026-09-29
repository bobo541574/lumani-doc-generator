<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_sources', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('project_id');
            $table->string('source_type', 50);
            $table->string('source_name', 255);
            $table->jsonb('source_config');
            $table->jsonb('mapping_config')->default(json_encode([
                'field_mappings' => (object) [],
                'transformations' => [],
                'defaults' => (object) [],
            ]));
            $table->string('sync_schedule', 50)->nullable();
            $table->timestampTz('last_sync_at')->nullable();
            $table->string('last_sync_status', 50)->nullable();
            $table->text('last_sync_error')->nullable();
            $table->integer('sync_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('source_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sources');
    }
};
