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

    // Xendit credentials (digunakan oleh InternalCredentialController)
    'xendit' => [
        'secret_key_live' => env('XENDIT_SECRET_KEY_LIVE'),
        'secret_key_test' => env('XENDIT_SECRET_KEY_TEST'),
        'webhook_token_live' => env('XENDIT_WEBHOOK_TOKEN_LIVE'),
        'webhook_token_test' => env('XENDIT_WEBHOOK_TOKEN_TEST'),
    ],

    // Komunikasi internal antar app di server yang sama (Static Bearer Token)
    'internal' => [
        'secret'              => env('APP2_INTERNAL_SECRET'),
        'webhook_url_ppab'    => env('INTERNAL_WEBHOOK_URL_PPAB'),
        'webhook_url_eyac'    => env('INTERNAL_WEBHOOK_URL_EYAC'),
        'webhook_url_archery' => env('INTERNAL_WEBHOOK_URL_ARCHERY'),
        'webhook_url_esii'    => env('INTERNAL_WEBHOOK_URL_ESII'),
    ],

];

