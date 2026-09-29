<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE activity_logs (
                id UUID DEFAULT gen_random_uuid(),
                organization_id UUID NOT NULL,
                user_id UUID,
                project_id UUID,
                document_id UUID,
                action VARCHAR(100) NOT NULL,
                details JSONB DEFAULT \'{}\',
                request_method VARCHAR(10),
                request_path VARCHAR(500),
                request_params JSONB,
                response_status INTEGER,
                ip_address INET,
                user_agent TEXT,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
                PRIMARY KEY (id, created_at)
            ) PARTITION BY RANGE (created_at)
        ');

        DB::statement('CREATE INDEX idx_activity_logs_organization_id ON activity_logs(organization_id)');
        DB::statement('CREATE INDEX idx_activity_logs_action ON activity_logs(action)');
        DB::statement('CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id)');
        DB::statement('CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
