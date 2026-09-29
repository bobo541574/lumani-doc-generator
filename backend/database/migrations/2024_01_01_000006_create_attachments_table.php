<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('document_id');
            $table->string('file_name', 255);
            $table->string('file_key', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('file_url', 1000)->nullable();
            $table->string('thumbnail_url', 1000)->nullable();
            $table->string('thumbnail_key', 500)->nullable();
            $table->string('type', 50)->default('file');
            $table->string('alt_text', 500)->nullable();
            $table->jsonb('metadata')->default(json_encode([
                'width' => null,
                'height' => null,
                'duration' => null,
                'bitrate' => null,
                'pages' => null,
                'dimensions' => null,
                'exif' => (object) [],
                'ocr' => (object) [],
            ]));
            $table->text('extracted_text')->nullable();
            $table->string('processing_status', 50)->default('pending');
            $table->text('processing_error')->nullable();
            $table->timestampTz('processing_started_at')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->index('processing_status');
            $table->index('mime_type');
            $table->index('type');
            $table->index('created_at');
        });

        DB::statement('CREATE INDEX idx_attachments_pending ON attachments(processing_status) WHERE processing_status = \'pending\'');
        DB::statement('CREATE INDEX idx_attachments_failed ON attachments(processing_status) WHERE processing_status = \'failed\'');
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
