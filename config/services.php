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

    'ovh' => [
        'api_key' => env('OVH_API_KEY'),
        'api_secret' => env('OVH_API_SECRET'),
        'api_endpoint' => env('OVH_API_ENDPOINT', 'ovh-eu'),
        'consumer_key' => env('OVH_API_CONSUMER_KEY'),
    ],

    'azure' => [
        'client_id' => env('AZURE_AD_CLIENT_ID'),
        'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
        'redirect' => env('AZURE_AD_REDIRECT_URI'),
        'tenant' => env('AZURE_AD_TENANT_ID'),
        'proxy' => env('PROXY'),
    ],

];
