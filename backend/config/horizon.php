<?php

declare(strict_types=1);

use Laravel\Horizon\Supervisor;

return [
    'defaults' => [
        'sup' => [
            'balance' => 'auto',
            'maxProcesses' => 5,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],

    'environments' => [
        'local' => [
            'supervisor-local' => [
                'connection' => 'redis',
                'queue' => ['default', 'ingestion', 'attachments', 'webhooks', 'ai'],
                'balance' => 'auto',
                'maxProcesses' => 8,
            ],
        ],

        'production' => [
            'supervisor-ingestion' => [
                'connection' => 'redis',
                'queue' => ['ingestion'],
                'balance' => 'auto',
                'processes' => 10,
                'tries' => 3,
                'timeout' => 300,
            ],
            'supervisor-attachments' => [
                'connection' => 'redis',
                'queue' => ['attachments'],
                'balance' => 'auto',
                'processes' => 6,
                'tries' => 3,
                'timeout' => 600,
            ],
            'supervisor-webhooks' => [
                'connection' => 'redis',
                'queue' => ['webhooks'],
                'balance' => 'auto',
                'processes' => 4,
                'tries' => 3,
                'timeout' => 30,
            ],
            'supervisor-ai' => [
                'connection' => 'redis',
                'queue' => ['ai'],
                'balance' => 'auto',
                'processes' => 4,
                'tries' => 3,
                'timeout' => 300,
            ],
        ],
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 120,
        'completed' => 60,
        'recent_failed' => 60,
        'failed' => 100800,
        'monitored' => 100800,
    ],

    'silenced' => [
        'App\Jobs\SendWebhookJob',
        'App\Jobs\ProcessAttachmentJob',
    ],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => true,
];
