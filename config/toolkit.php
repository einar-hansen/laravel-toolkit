<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Toolkit configuration
    |--------------------------------------------------------------------------
    |
    */
    'eloquent' => [
        'strict_mode' => true,
        'eager_load_relationships' => false,
    ],
    'app' => [
        'enable_aggressive_prefetching' => true,
        'enforce_https_scheme' => true,
        'enable_immutable_dates' => true,
        'disable_destructive_commands' => true,
        'use_default_password' => true,
    ],
    'tests' => [
        'enable_fake_sleep' => true,
        'prevent_stray_requests' => true,
    ],
];
