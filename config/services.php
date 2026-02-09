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
    | OpenAI API (Whisper Transcription & Embeddings)
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embeddings Configuration (Vector Search)
    |--------------------------------------------------------------------------
    |
    | Configuration for generating embeddings for semantic/vector search.
    | Uses OpenAI's text-embedding-3-small model by default.
    |
    */
    'embeddings' => [
        'provider' => env('EMBEDDINGS_PROVIDER', 'openai'),
        'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => (int) env('OPENAI_EMBEDDING_DIMENSIONS', 1536),
        'auto_generate' => env('EMBEDDINGS_AUTO_GENERATE', true),
        'max_tokens' => (int) env('EMBEDDINGS_MAX_TOKENS', 8191),
        'batch_size' => (int) env('EMBEDDINGS_BATCH_SIZE', 100),
        'queue' => env('EMBEDDINGS_QUEUE', 'embeddings'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Moderation Configuration
    |--------------------------------------------------------------------------
    |
    | AI-powered content moderation for K-12 educational content.
    | Evaluates age appropriateness, clinical safety, cultural sensitivity,
    | and accuracy of AI-generated content.
    |
    */
    'moderation' => [
        'enabled' => env('CONTENT_MODERATION_ENABLED', true),
        'auto_moderate' => env('CONTENT_MODERATION_AUTO', true),
        'model' => env('CONTENT_MODERATION_MODEL', 'claude-sonnet-4-20250514'),
        'queue' => env('CONTENT_MODERATION_QUEUE', 'moderation'),
        'thresholds' => [
            'auto_pass' => (float) env('MODERATION_THRESHOLD_PASS', 0.85),
            'flag_for_review' => (float) env('MODERATION_THRESHOLD_FLAG', 0.70),
            'auto_reject' => (float) env('MODERATION_THRESHOLD_REJECT', 0.40),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AssemblyAI API (Transcription)
    |--------------------------------------------------------------------------
    */
    'assembly_ai' => [
        'api_key' => env('ASSEMBLY_AI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transcription Service Configuration
    |--------------------------------------------------------------------------
    */
    'transcription' => [
        'default' => env('TRANSCRIPTION_PROVIDER', 'whisper'),
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
        'sheets' => [
            'spreadsheet_id' => env('GOOGLE_SHEETS_ID'),
            'sheet_name' => env('GOOGLE_SHEETS_NAME', 'Sheet1'),
            'credentials' => env('GOOGLE_SHEETS_CREDENTIALS', ''),
        ],
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

    /*
    |--------------------------------------------------------------------------
    | GetStream Chat
    |--------------------------------------------------------------------------
    */
    'stream' => [
        'api_key' => env('STREAM_API_KEY'),
        'api_secret' => env('STREAM_API_SECRET'),
        'app_id' => env('STREAM_APP_ID'),
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailgun Email
    |--------------------------------------------------------------------------
    */
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'zoho' => [
        'flow_webhook_url' => env('ZOHO_FLOW_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Processing
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
