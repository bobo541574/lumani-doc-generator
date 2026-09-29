<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('organization_id');
            $table->string('email', 255);
            $table->string('name', 255);
            $table->string('password_hash', 255)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->string('role', 50)->default('editor');
            $table->jsonb('permissions')->default('[]');
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->jsonb('preferences')->default(json_encode([
                'theme' => 'light',
                'language' => 'en',
                'notifications' => [
                    'email' => true,
                    'web' => true,
                ],
            ]));
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
            $table->softDeletesTz();

            $table->unique(['organization_id', 'email']);
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->index('email');
            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
