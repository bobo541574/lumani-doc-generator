<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Driver
    |--------------------------------------------------------------------------
    | supported: none, openai, anthropic
    */
    'driver' => env('AI_DRIVER', 'none'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
    ],

    'generation' => [
        'max_tokens' => env('AI_MAX_TOKENS', 4096),
        'temperature' => env('AI_TEMPERATURE', 0.7),
        'timeout_seconds' => env('AI_TIMEOUT_SECONDS', 120),
    ],
];
