<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'iam' => [
        'base_url' => env('IAM_BASE_URL', 'http://localhost:8000'),
        'client_id' => env('IAM_CLIENT_ID'),
        'client_secret' => env('IAM_CLIENT_SECRET'),
        'redirect_uri' => env('IAM_REDIRECT_URI', env('APP_URL', 'http://localhost:8001').'/auth/sso/callback'),
        'authorize_endpoint' => '/oauth/authorize',
        'token_endpoint' => '/oauth/token',
        'userinfo_endpoint' => '/api/user',
    ],

];
