<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Toolkit configuration
    |--------------------------------------------------------------------------
    |
    | Here you can select which mixins and configs you want to enable.
    |
    */

    'eloquent' => [
        'strict_mode' => (bool) env('ELOQUENT_STRICT_MODE', true),
        'eager_load_relationships' => (bool) env('ELOQUENT_EAGER_LOAD_RELATIONSHIPS', false),
    ],
    'app' => [
        'enable_aggressive_prefetching' => (bool) env('APP_ENABLE_AGGRESSIVE_PREFETCHING', true),
        'enforce_https_scheme' => (bool) env('APP_ENFORCE_HTTPS_SCHEME', true),
        'enable_immutable_dates' => (bool) env('APP_ENABLE_IMMUTABLE_DATES', true),
        'disable_destructive_commands' => (bool) env('APP_DISABLE_DESTRUCTIVE_COMMANDS', true),
        'use_default_password' => (bool) env('APP_USE_DEFAULT_PASSWORD', true),
    ],
    'tests' => [
        'enable_fake_sleep' => (bool) env('TESTS_ENABLE_FAKE_SLEEP', true),
        'prevent_stray_requests' => (bool) env('TESTS_PREVENT_STRAY_REQUESTS', true),
    ],
    'mixins' => [
        'arr' => (bool) env('MIXIN_REGISTER_ARR', true),
        'collection' => (bool) env('MIXIN_REGISTER_COLLECTION', true),
        'response' => (bool) env('MIXIN_REGISTER_RESPONSE', true),
    ],
];
