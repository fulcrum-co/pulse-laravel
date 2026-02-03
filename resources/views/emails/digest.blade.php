<!DOCTYPE html>
<html lang="en">
@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $terminology->get('email_digest_title_prefix') }} {{ ucfirst($digestType) }} {{ $terminology->get('email_digest_title_suffix') }}</title>
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
        .header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        .content {
            padding: 24px;
        }
        .greeting {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1;
            min-width: 120px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #f97316;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .category-section {
            margin-bottom: 24px;
        }
        .category-header {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        .category-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            flex: 1;
        }
        .category-count {
            font-size: 12px;
            color: #6b7280;
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 9999px;
        }
        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notification-item {
            padding: 12px 16px;
            border-left: 3px solid #e5e7eb;
            margin-bottom: 8px;
            background: #fafafa;
            border-radius: 0 6px 6px 0;
        }
        .notification-item.priority-high {
            border-left-color: #fbbf24;
            background: #fffbeb;
        }
        .notification-item.priority-urgent {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 4px 0;
        }
        .notification-body {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notification-meta {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 6px;
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
            margin-top: 16px;
        }
        .button:hover {
            background: #ea580c;
        }
        .button-secondary {
            background: #f3f4f6;
            color: #374151 !important;
        }
        .button-secondary:hover {
            background: #e5e7eb;
        }
        .actions {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
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
        .more-count {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>{{ $terminology->get('app_name_label') }}</h1>
                <div class="subtitle">{{ $terminology->get('email_digest_subtitle_prefix') }} {{ ucfirst($digestType) }} {{ $terminology->get('email_digest_subtitle_suffix') }}</div>
            </div>

            <div class="content">
                <p class="greeting">{{ $terminology->get('email_greeting_label') }} {{ $user->first_name ?? $terminology->get('email_greeting_fallback_label') }},</p>

                <p style="color: #4b5563; margin-bottom: 24px;">
                    {{ $terminology->get('email_digest_summary_prefix') }} {{ $digestType === 'weekly' ? $terminology->get('email_digest_week_label') : $terminology->get('email_digest_day_label') }}.
                </p>

                {{-- Summary Stats --}}
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-number">{{ $totalCount }}</div>
                        <div class="stat-label">{{ $terminology->get('email_digest_total_label') }}</div>
                    </div>
                    @if($highPriorityCount > 0)
                    <div class="stat-box">
                        <div class="stat-number" style="color: #dc2626;">{{ $highPriorityCount }}</div>
                        <div class="stat-label">{{ $terminology->get('email_digest_high_priority_label') }}</div>
                    </div>
                    @endif
                    <div class="stat-box">
                        <div class="stat-number">{{ $groupedNotifications->count() }}</div>
                        <div class="stat-label">{{ $terminology->get('email_digest_categories_label') }}</div>
                    </div>
                </div>

                {{-- Notifications by Category --}}
                @foreach($groupedNotifications as $category => $notifications)
                    <div class="category-section">
                        <div class="category-header">
                            <span class="category-title">{{ $categoryLabels[$category] ?? ucfirst($category) }}</span>
                            <span class="category-count">{{ $notifications->count() }}</span>
                        </div>

                        <ul class="notification-list">
                            @foreach($notifications->take(5) as $notification)
                                <li class="notification-item priority-{{ $notification->priority }}">
                                    <p class="notification-title">{{ $notification->title }}</p>
                                    @if($notification->body)
                                        <p class="notification-body">{{ Str::limit($notification->body, 100) }}</p>
                                    @endif
                                    <div class="notification-meta">
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if(in_array($notification->priority, ['high', 'urgent']))
                                            &bull; <span style="color: {{ $notification->priority === 'urgent' ? '#dc2626' : '#d97706' }};">
                                                {{ ucfirst($notification->priority) }} {{ $terminology->get('priority_label') }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if($notifications->count() > 5)
                            <p class="more-count">
                                + {{ $notifications->count() - 5 }} {{ $terminology->get('email_digest_more_in_category_label') }}
                            </p>
                        @endif
                    </div>
                @endforeach

                <div class="actions">
                    <a href="{{ $notificationCenterUrl }}" class="button">
                        {{ $terminology->get('email_digest_view_all_label') }}
                    </a>
                </div>
            </div>

            <div class="footer">
                <p>
                    {{ $terminology->get('email_digest_receiving_prefix') }} {{ $digestType }} {{ $terminology->get('email_digest_receiving_suffix') }}
                    <br>
                    <a href="{{ $preferencesUrl }}">{{ $terminology->get('email_change_preferences_label') }}</a>
                    &bull;
                    <a href="{{ $unsubscribeUrl }}">{{ $terminology->get('email_unsubscribe_digests_label') }}</a>
                </p>
                <p style="margin-top: 16px;">
                    &copy; {{ date('Y') }} {{ $terminology->get('email_footer_brand_label') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
