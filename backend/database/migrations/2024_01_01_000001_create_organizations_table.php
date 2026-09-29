<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('domain', 255)->unique()->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->jsonb('config')->default(json_encode([
                'theme' => 'default',
                'timezone' => 'UTC',
                'date_format' => 'YYYY-MM-DD',
                'features' => [
                    'ai_enabled' => true,
                    'webhooks_enabled' => true,
                    'api_access' => true,
                ],
            ]));
            $table->string('subscription_tier', 50)->default('free');
            $table->timestampTz('subscription_expires_at')->nullable();
            $table->jsonb('settings')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->index('subscription_tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
