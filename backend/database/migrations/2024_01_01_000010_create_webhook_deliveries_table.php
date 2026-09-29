<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('webhook_id');
            $table->string('event', 100);
            $table->jsonb('payload');
            $table->integer('response_status')->nullable();
            $table->jsonb('response_headers')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_stack')->nullable();
            $table->timestampTz('delivered_at')->default(DB::raw('NOW()'));
            $table->integer('retry_count')->default(0);
            $table->boolean('success')->default(false);

            $table->foreign('webhook_id')->references('id')->on('webhooks')->onDelete('cascade');
            $table->index('delivered_at');
            $table->index('success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
