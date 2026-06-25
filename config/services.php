<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'nubefact' => [
        'token' => env('NUBEFACT_TOKEN', ''),
        'ruc'   => env('NUBEFACT_RUC', ''),
        'url'   => env('NUBEFACT_URL', 'https://demo-facturacion.nubefact.com/api/v1'),
    ],
    'culqi' => [
        'public_key'     => env('CULQI_PUBLIC_KEY', ''),
        'secret_key'     => env('CULQI_SECRET_KEY', ''),
        'webhook_secret' => env('CULQI_WEBHOOK_SECRET', ''),
    ],
];
