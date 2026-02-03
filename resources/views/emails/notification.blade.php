<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #f97316;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .category {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-top: 4px;
        }
        .content {
            padding: 32px 24px;
        }
        .title {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #111827;
        }
        .body {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .priority-high {
            background: #fef3c7;
            color: #92400e;
        }
        .priority-urgent {
            background: #fee2e2;
            color: #991b1b;
        }
        .button {
            display: inline-block;
            background: #f97316;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .button:hover {
            background: #ea580c;
        }
        .footer {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #f97316;
            text-decoration: none;
        }
        .greeting {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    @php
        $terminology = app(\App\Services\TerminologyService::class);
    @endphp
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>{{ $terminology->get('app_name_label') }}</h1>
                <div class="category">{{ $categoryLabel }}</div>
            </div>

            <div class="content">
                @if($user)
                    <p class="greeting">{{ $terminology->get('email_greeting_label') }} {{ $user->first_name ?? $terminology->get('email_greeting_fallback_label') }},</p>
                @endif

                @if(in_array($priority, ['high', 'urgent']))
                    <span class="priority-badge priority-{{ $priority }}">
                        {{ ucfirst($priority) }} {{ $terminology->get('priority_label') }}
                    </span>
                @endif

                <h2 class="title">{{ $title }}</h2>

                @if($body)
                    <div class="body">
                        {!! nl2br(e($body)) !!}
                    </div>
                @endif

                @if($actionUrl)
                    <a href="{{ $actionUrl }}" class="button">
                        {{ $actionLabel }}
                    </a>
                @endif
            </div>

            <div class="footer">
                <p>
                    {{ $terminology->get('email_notifications_enabled_prefix') }} {{ strtolower($categoryLabel) }} {{ $terminology->get('email_notifications_enabled_suffix') }}
                    <br>
                    <a href="{{ url('/settings/notifications') }}">{{ $terminology->get('email_manage_notification_preferences_label') }}</a>
                </p>
                <p style="margin-top: 16px;">
                    &copy; {{ date('Y') }} {{ $terminology->get('email_footer_brand_label') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
