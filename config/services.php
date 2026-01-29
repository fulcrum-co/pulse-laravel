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

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude API
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
        'temperature' => (float) env('ANTHROPIC_TEMPERATURE', 0.7),
        'base_url' => 'https://api.anthropic.com/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sinch API (Voice/SMS/WhatsApp)
    |--------------------------------------------------------------------------
    */
    'sinch' => [
        'project_id' => env('SINCH_PROJECT_ID'),
        'key_id' => env('SINCH_KEY_ID'),
        'key_secret' => env('SINCH_KEY_SECRET'),
        'phone_number' => env('SINCH_PHONE_NUMBER'),
        'whatsapp_number' => env('SINCH_WHATSAPP_NUMBER'),
        'voice_url' => 'https://calling.api.sinch.com/v1',
        'sms_url' => 'https://us.sms.api.sinch.com/xms/v1',
        'whatsapp_url' => 'https://whatsapp.api.sinch.com/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth (SSO)
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft Azure AD (SSO)
    |--------------------------------------------------------------------------
    */
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
    ],

];
