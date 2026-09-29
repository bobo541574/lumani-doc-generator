<?php

declare(strict_types=1);

return [
    'enabled' => env('MCP_ENABLED', false),

    'server' => [
        'host' => env('MCP_HOST', '0.0.0.0'),
        'port' => env('MCP_PORT', 8765),
    ],

    'transport' => env('MCP_TRANSPORT', 'http'),

    'auth' => [
        'header' => env('MCP_AUTH_HEADER', 'Authorization'),
        'token' => env('MCP_AUTH_TOKEN'),
    ],

    'capabilities' => [
        'ingest' => env('MCP_CAP_INGEST', true),
        'query' => env('MCP_CAP_QUERY', true),
        'attachments' => env('MCP_CAP_ATTACHMENTS', true),
        'search' => env('MCP_CAP_SEARCH', true),
    ],
];
