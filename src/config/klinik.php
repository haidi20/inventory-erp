<?php

return [
    'runtime_environment' => env('KLINIK_RUNTIME_ENV', env('APP_ENV', 'production')),

    'sandbox_environments' => [
        'local',
        'development',
        'dev',
        'sandbox',
        'staging',
        'testing',
    ],

    'headers' => [
        'environment' => 'X-Klinik-Environment',
        'company_uuid' => 'X-Klinik-Company-Uuid',
    ],

    'runtime_branches' => [
        'sandbox' => env('KLINIK_SANDBOX_BRANCH_SHORT_NAME', 'KSB'),
        'production' => env('KLINIK_PRODUCTION_BRANCH_SHORT_NAME', 'KPR'),
    ],

    'testing' => [
        'base_url' => env('KLINIK_TEST_BASE_URL', env('APP_URL', 'http://localhost:8000')),
    ],
];
