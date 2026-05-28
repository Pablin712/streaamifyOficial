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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'n8n' => [
        'payment_webhook' => env('N8N_PAYMENT_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/94d871f4-6485-4118-93ec-471171de71c7'),
        'payment_webhook_secret' => env('N8N_PAYMENT_WEBHOOK_SECRET'),
        'account_message_webhook' => env('N8N_ACCOUNT_MESSAGE_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/cuenta-mensajear'),
        'client_message_webhook' => env('N8N_CLIENT_MESSAGE_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/mensaje-cliente'),
        'mass_client_message_webhook' => env('N8N_MASS_CLIENT_MESSAGE_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/masivo'),
        'netflix_code_webhook' => env('N8N_NETFLIX_CODE_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/pedir-codigo'),
        'chat_webhook_token' => env('CHAT_WEBHOOK_TOKEN'),
        'webhook_token' => env('N8N_WEBHOOK_TOKEN', 'laravel_token_123'),
        'outbound_webhook' => env('N8N_OUTBOUND_WEBHOOK', 'https://autobot.aaronsoft.es/webhook/laravel-outbound'),
    ],

    'streamify' => [
        'public_site_url' => env('STREAMIFY_PUBLIC_SITE_URL', 'https://streamify.aaronsoft.es/public/'),
    ],

    'evoapi' => [
        'base_url' => env('EVOAPI_BASE_URL', 'https://evoapi.abigailsoft.com'),
        'timeout_seconds' => env('EVOAPI_TIMEOUT_SECONDS', 20),
        'instance_aliases' => [
            // Alias operativo para el canal azul cuando llega "default" desde n8n.
            'default' => 'Streamify Azul',
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => rtrim(env('APP_URL', 'http://localhost'), '/') . '/cliente/donna/google/callback',
    ],

    'donna' => [
        'api_key'                => env('DONNA_API_KEY'),
        'telegram_bot_username'  => env('DONNA_TELEGRAM_BOT_USERNAME', 'DonnaStreamifyBot'),
        'personal_payment_url'   => env('DONNA_PERSONAL_PAYMENT_URL', env('APP_URL') . '/donna'),
        'google_default_timezone'=> env('DONNA_GOOGLE_DEFAULT_TIMEZONE', 'America/Guayaquil'),
        'expose_google_token'    => env('DONNA_EXPOSE_GOOGLE_ACCESS_TOKEN', false),
    ],

    'netflix_code' => [
        'provider_name' => env('NETFLIX_CODE_PROVIDER_NAME', 'Alejandro Guevara'),
        'service_id' => env('NETFLIX_CODE_SERVICE_ID', 'NETFLIX'),
        'code_expires_minutes' => env('NETFLIX_CODE_EXPIRES_MINUTES', 15),
        'timeout_seconds' => env('NETFLIX_CODE_TIMEOUT_SECONDS', 70),
    ],

];


