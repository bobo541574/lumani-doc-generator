<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('project_id');
            $table->string('name', 255);
            $table->string('url', 1000);
            $table->jsonb('events');
            $table->string('secret', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('retry_count')->default(3);
            $table->integer('timeout_ms')->default(5000);
            $table->jsonb('headers')->default('{}');
            $table->timestampTz('last_triggered_at')->nullable();
            $table->string('last_triggered_status', 50)->nullable();
            $table->text('last_error_message')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
