<?php

declare(strict_types=1);

return [
    'default_disk' => env('STORAGE_DEFAULT_DISK', 'local'),

    'attachments_disk' => env('STORAGE_ATTACHMENTS_DISK', 'minio'),

    'public_disk' => env('STORAGE_PUBLIC_DISK', 'public'),

    'attachment_max_size' => env('ATTACHMENT_MAX_SIZE', 52428800),
];
