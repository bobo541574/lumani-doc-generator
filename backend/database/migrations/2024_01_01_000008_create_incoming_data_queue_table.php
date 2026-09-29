<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE incoming_data_queue (
                id UUID DEFAULT gen_random_uuid(),
                project_id UUID NOT NULL,
                source_id UUID,
                sync_id VARCHAR(255),
                payload JSONB NOT NULL,
                target_document_id UUID,
                action VARCHAR(50) DEFAULT \'create\',
                status VARCHAR(50) DEFAULT \'pending\',
                progress INTEGER DEFAULT 0,
                error_message TEXT,
                error_stack TEXT,
                processed_at TIMESTAMP WITH TIME ZONE,
                retry_count INTEGER DEFAULT 0,
                max_retries INTEGER DEFAULT 3,
                next_retry_at TIMESTAMP WITH TIME ZONE,
                metadata JSONB DEFAULT \'{ "processing_time": null, "memory_usage": null, "steps_completed": [] }\',
                created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
                PRIMARY KEY (id, created_at),
                UNIQUE(project_id, sync_id, created_at)
            ) PARTITION BY RANGE (created_at)
        ');

        DB::statement('CREATE INDEX idx_incoming_data_queue_project_id ON incoming_data_queue(project_id)');
        DB::statement('CREATE INDEX idx_incoming_data_queue_status ON incoming_data_queue(status)');
        DB::statement('CREATE INDEX idx_incoming_data_queue_sync_id ON incoming_data_queue(sync_id)');
        DB::statement('CREATE INDEX idx_incoming_data_queue_created_at ON incoming_data_queue(created_at)');
        DB::statement('CREATE INDEX idx_incoming_data_queue_status_created ON incoming_data_queue(status, created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_data_queue');
    }
};
