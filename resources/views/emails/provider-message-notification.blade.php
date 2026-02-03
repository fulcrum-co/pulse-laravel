<!DOCTYPE html>
<html>
@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $terminology->get('email_provider_message_title') }} - {{ $terminology->get('app_name_label') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 32px;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo span {
            font-size: 28px;
            font-weight: bold;
            color: #f97316;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 16px 0;
        }
        .message-box {
            background: #f3f4f6;
            border-left: 4px solid #f97316;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .message-box .sender {
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
        .message-box .preview {
            color: #6b7280;
            font-style: italic;
        }
        .button {
            display: inline-block;
            background: #f97316;
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
        }
        .button:hover {
            background: #ea580c;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 14px;
        }
        .footer a {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <span>{{ $terminology->get('app_name_label') }}</span>
            </div>

            <h1>{{ $terminology->get('email_greeting_label') }} {{ $providerName }},</h1>

            <p>{{ $terminology->get('email_provider_message_intro') }} {{ $terminology->get('app_name_label') }}:</p>

            <div class="message-box">
                <div class="sender">{{ $senderName }}</div>
                <div class="preview">"{{ $messagePreview }}"</div>
            </div>

            <p>{{ $terminology->get('email_provider_message_cta_prompt') }}</p>

            <div style="text-align: center;">
                <a href="{{ $replyLink }}" class="button">{{ $terminology->get('email_provider_message_cta_label') }}</a>
            </div>

            <p style="color: #6b7280; font-size: 14px;">
                {{ $terminology->get('email_provider_message_expiry_notice') }}
            </p>
        </div>

        <div class="footer">
            <p>
                {{ $terminology->get('email_provider_message_footer_reason') }}<br>
                <a href="#">{{ $terminology->get('email_manage_notification_preferences_label') }}</a>
            </p>
            <p>&copy; {{ date('Y') }} {{ $terminology->get('email_footer_brand_label') }} {{ $terminology->get('email_rights_reserved_label') }}</p>
        </div>
    </div>
</body>
</html>
