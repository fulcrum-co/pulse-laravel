<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Reminder</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 48px;
            height: 48px;
        }
        .content {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 12px 0;
        }
        .message {
            color: #4b5563;
            margin-bottom: 20px;
        }
        .session-info {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .session-label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .session-value {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-top: 4px;
        }
        .button {
            display: inline-block;
            background-color: #f97316;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
        }
        .button:hover {
            background-color: #ea580c;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/pulse-logo.png') }}" alt="Pulse" class="logo" />
    </div>

    <div class="content">
        <h1 class="title">{{ $collection->title }}</h1>

        <p class="message">{{ $message }}</p>

        @if($session)
        <div class="session-info">
            <div class="session-label">Session Date</div>
            <div class="session-value">{{ $session->session_date->format('l, F j, Y') }}</div>
        </div>
        @endif

        <a href="{{ $actionUrl }}" class="button">
            Start Collection
        </a>
    </div>

    <div class="footer">
        <p>You're receiving this because you're assigned to this data collection.</p>
        <p>
            <a href="{{ route('settings.index') }}">Manage notification preferences</a>
        </p>
    </div>
</body>
</html>
