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
        'strict_mode' => env('ELOQUENT_STRICT_MODE', true),
        'eager_load_relationships' => env('ELOQUENT_EAGER_LOAD_RELATIONSHIPS', false),
    ],
    'app' => [
        'enable_aggressive_prefetching' => env('APP_ENABLE_AGGRESSIVE_PREFETCHING', true),
        'enforce_https_scheme' => env('APP_ENFORCE_HTTPS_SCHEME', true),
        'enable_immutable_dates' => env('APP_ENABLE_IMMUTABLE_DATES', true),
        'disable_destructive_commands' => env('APP_DISABLE_DESTRUCTIVE_COMMANDS', true),
        'use_default_password' => env('APP_USE_DEFAULT_PASSWORD', true),
    ],
    'tests' => [
        'enable_fake_sleep' => env('TESTS_ENABLE_FAKE_SLEEP', true),
        'prevent_stray_requests' => env('TESTS_PREVENT_STRAY_REQUESTS', true),
    ],
];
