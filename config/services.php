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
    ],

    'streamify' => [
        'public_site_url' => env('STREAMIFY_PUBLIC_SITE_URL', 'https://streamify.aaronsoft.es/public/'),
    ],

];
