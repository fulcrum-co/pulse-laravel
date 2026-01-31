<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Message - Pulse</title>
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
                <span>Pulse</span>
            </div>

            <h1>Hi {{ $providerName }},</h1>

            <p>You have a new message on Pulse:</p>

            <div class="message-box">
                <div class="sender">{{ $senderName }}</div>
                <div class="preview">"{{ $messagePreview }}"</div>
            </div>

            <p>Click the button below to view the full message and reply:</p>

            <div style="text-align: center;">
                <a href="{{ $replyLink }}" class="button">View & Reply</a>
            </div>

            <p style="color: #6b7280; font-size: 14px;">
                This link will expire in 7 days. If you have any questions, please contact support.
            </p>
        </div>

        <div class="footer">
            <p>
                You're receiving this because you're registered as a provider on Pulse.<br>
                <a href="#">Manage notification preferences</a>
            </p>
            <p>&copy; {{ date('Y') }} Pulse. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
