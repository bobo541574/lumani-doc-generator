<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('document_id');
            $table->string('version', 20);
            $table->text('content');
            $table->text('change_summary')->nullable();
            $table->jsonb('metadata')->default(json_encode([
                'changes' => [],
                'impact' => 'minor',
                'breaking_changes' => false,
                'deprecations' => [],
            ]));
            $table->uuid('created_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));

            $table->unique(['document_id', 'version']);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
