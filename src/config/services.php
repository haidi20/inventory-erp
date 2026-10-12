<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'bpjs' => [
        'base_url' => env('BPJS_BASE_URL', 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest'),
        'consumer_id' => env('BPJS_CONSUMER_ID'),
        'user_key' => env('BPJS_USER_KEY'),
        'secret_key' => env('BPJS_SECRET_KEY'),
        'ppk_code' => env('BPJS_PPK_CODE'),
        'is_active' => env('BPJS_IS_ACTIVE', false),
    ],

    'satusehat' => [
        'org_id' => env('SATUSEHAT_ORG_ID'),
        'client_id' => env('SATUSEHAT_CLIENT_ID'),
        'client_secret' => env('SATUSEHAT_CLIENT_SECRET'),
        'base_url' => env('SATUSEHAT_BASE_URL', 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1'),
        'auth_url' => env('SATUSEHAT_AUTH_URL', 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

];
