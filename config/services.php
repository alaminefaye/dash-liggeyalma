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

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'wave' => [
        'api_key' => env('WAVE_API_KEY'),
        'merchant_key' => env('WAVE_MERCHANT_KEY'),
        'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com/v1'),
    ],

    'orange_money' => [
        'merchant_id' => env('ORANGE_MONEY_MERCHANT_ID'),
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
        'base_url' => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com/orange-money-webpay'),
    ],

    'mtn' => [
        'api_key' => env('MTN_API_KEY'),
        'api_secret' => env('MTN_API_SECRET'),
        'subscription_key' => env('MTN_SUBSCRIPTION_KEY'),
        'environment' => env('MTN_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
    ],

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
