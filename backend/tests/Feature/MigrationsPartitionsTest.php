<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('all core tables were created by migrations', function () {
    $tables = [
        'organizations',
        'users',
        'projects',
        'documents',
        'document_versions',
        'attachments',
        'external_sources',
        'incoming_data_queue',
        'webhooks',
        'webhook_deliveries',
        'api_keys',
        'activity_logs',
        'sessions',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("expected table [$table] to exist");
    }
});

test('partitions command provisions the default catch-all partitions', function () {
    $names = DB::select(
        "SELECT inhrelid::regclass AS child FROM pg_inherits
         WHERE inhparent::regclass = 'activity_logs'::regclass"
    );

    expect(array_column($names, 'child'))->toContain('activity_logs_default');
});
