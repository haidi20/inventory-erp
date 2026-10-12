<?php

return [

    'enabled' => env('AUDITING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Audit Implementation
    |--------------------------------------------------------------------------
    */

    'implementation' => App\Shared\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | User Morph prefix & Guards
    |--------------------------------------------------------------------------
    */

    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'web',
            'api',
        ],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Resolvers
    | Custom resolvers ditambahkan untuk inject company_uuid & branch_uuid
    | ke setiap record audit â€” diperlukan untuk compliance & multi-org filtering.
    |--------------------------------------------------------------------------
    */

    'resolvers' => [
        'ip_address'   => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent'   => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url'          => OwenIt\Auditing\Resolvers\UrlResolver::class,
        'company_uuid' => App\Shared\Auditing\Resolvers\CompanyUuidResolver::class,
        'branch_uuid'  => App\Shared\Auditing\Resolvers\BranchUuidResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Events
    |--------------------------------------------------------------------------
    */

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    */

    'strict' => false,

    /*
    |--------------------------------------------------------------------------
    | Global exclude
    |--------------------------------------------------------------------------
    */

    'exclude' => [],

    /*
    |--------------------------------------------------------------------------
    | Empty Values
    |--------------------------------------------------------------------------
    */

    'empty_values' => true,
    'allowed_empty_values' => [
        'retrieved',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Array Values
    |--------------------------------------------------------------------------
    */

    'allowed_array_values' => false,

    /*
    |--------------------------------------------------------------------------
    | Audit Timestamps
    |--------------------------------------------------------------------------
    */

    'timestamps' => false,

    /*
    |--------------------------------------------------------------------------
    | Audit Threshold
    |--------------------------------------------------------------------------
    */

    'threshold' => 0,

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    */

    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Audit Driver Configurations
    |--------------------------------------------------------------------------
    */

    'drivers' => [
        'database' => [
            'table' => 'audits',
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Queue Configurations
    | Set AUDITING_QUEUE=true di .env untuk async audit writes (recommended production)
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'enable'     => env('AUDITING_QUEUE', false),
        'connection' => env('AUDITING_QUEUE_CONNECTION', 'sync'),
        'queue'      => env('AUDITING_QUEUE_NAME', 'default'),
        'delay'      => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Console
    |--------------------------------------------------------------------------
    */

    'console' => false,
];

