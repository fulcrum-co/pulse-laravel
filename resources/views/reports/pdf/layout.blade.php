<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $branding['font_family'] ?? 'Helvetica, Arial, sans-serif' }};
            font-size: 12px;
            line-height: 1.5;
            color: #1f2937;
            background: white;
        }

        .page {
            position: relative;
            width: 100%;
            min-height: 100%;
            padding: {{ $report->getPageSettings()['margins']['top'] ?? 40 }}px;
            padding-right: {{ $report->getPageSettings()['margins']['right'] ?? 40 }}px;
            padding-bottom: {{ $report->getPageSettings()['margins']['bottom'] ?? 40 }}px;
            padding-left: {{ $report->getPageSettings()['margins']['left'] ?? 40 }}px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid {{ $branding['primary_color'] ?? '#3B82F6' }};
        }

        .logo {
            height: 40px;
        }

        .header-text {
            text-align: right;
        }

        .header-text .org-name {
            font-size: 14px;
            font-weight: bold;
            color: {{ $branding['primary_color'] ?? '#3B82F6' }};
        }

        .header-text .date {
            font-size: 10px;
            color: #6b7280;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 10px;
        }

        .report-description {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .element {
            position: absolute;
            overflow: hidden;
        }

        .element-content {
            width: 100%;
            height: 100%;
        }

        /* Text elements */
        .text-element h1 { font-size: 20px; font-weight: bold; margin-bottom: 8px; }
        .text-element h2 { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .text-element h3 { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .text-element p { margin-bottom: 8px; }
        .text-element ul, .text-element ol { margin-left: 20px; margin-bottom: 8px; }

        /* Metric cards */
        .metric-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .metric-card .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        .metric-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-top: 4px;
        }

        .metric-card .trend {
            font-size: 10px;
            margin-top: 4px;
        }

        .metric-card .trend.positive { color: #10b981; }
        .metric-card .trend.negative { color: #ef4444; }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data-table th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            color: #374151;
        }

        .data-table td {
            border-bottom: 1px solid #f3f4f6;
            padding: 8px;
            color: #4b5563;
        }

        /* Charts (placeholder for images) */
        .chart-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chart-title {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .chart-image {
            flex: 1;
            background: #f9fafb;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 10px;
        }

        .chart-image img {
            max-width: 100%;
            max-height: 100%;
        }

        /* AI Text */
        .ai-text {
            font-size: 11px;
            color: #374151;
            line-height: 1.6;
        }

        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 9px;
            color: #7c3aed;
            margin-bottom: 8px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div>
                @if($branding['logo_path'] ?? null)
                    <img src="{{ $branding['logo_path'] }}" alt="Logo" class="logo">
                @else
                    <span style="font-size: 18px; font-weight: bold; color: {{ $branding['primary_color'] ?? '#3B82F6' }}">
                        {{ $report->organization->name ?? 'Pulse' }}
                    </span>
                @endif
            </div>
            <div class="header-text">
                <div class="org-name">{{ $report->organization->name ?? '' }}</div>
                <div class="date">Generated: {{ now()->format('F j, Y') }}</div>
            </div>
        </div>

        <!-- Title -->
        <h1 class="report-title">{{ $report->report_name }}</h1>
        @if($report->report_description)
            <p class="report-description">{{ $report->report_description }}</p>
        @endif

        <!-- Elements -->
        <div style="position: relative; min-height: 600px;">
            @foreach($elements as $element)
                @php
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 200;
                    $height = $element['size']['height'] ?? 100;
                    $bgColor = $element['styles']['backgroundColor'] ?? 'transparent';
                    $borderRadius = $element['styles']['borderRadius'] ?? 0;
                    $padding = $element['styles']['padding'] ?? 0;
                @endphp

                <div
                    class="element"
                    style="
                        left: {{ $x }}px;
                        top: {{ $y }}px;
                        width: {{ $width }}px;
                        height: {{ $height }}px;
                        background-color: {{ $bgColor }};
                        border-radius: {{ $borderRadius }}px;
                        padding: {{ $padding }}px;
                        @if(isset($element['styles']['borderWidth']) && $element['styles']['borderWidth'] > 0)
                            border: {{ $element['styles']['borderWidth'] }}px solid {{ $element['styles']['borderColor'] ?? '#E5E7EB' }};
                        @endif
                    "
                >
                    @switch($element['type'])
                        @case('text')
                            <div class="element-content text-element">
                                {!! $element['config']['content'] ?? '' !!}
                            </div>
                            @break

                        @case('metric_card')
                            @php
                                $metricKey = $element['config']['metric_key'] ?? '';
                                $metricData = $data['metrics'][$metricKey] ?? $data['aggregates'][$metricKey] ?? null;
                                $value = $metricData['value'] ?? $metricData['average'] ?? '--';
                            @endphp
                            <div class="metric-card">
                                <span class="label">{{ $element['config']['label'] ?? ucwords(str_replace('_', ' ', $metricKey)) }}</span>
                                <span class="value">{{ is_numeric($value) ? number_format($value, 2) : $value }}</span>
                                @if($element['config']['show_trend'] ?? false)
                                    <span class="trend positive">+0.0%</span>
                                @endif
                            </div>
                            @break

                        @case('chart')
                            <div class="chart-container">
                                @if($element['config']['title'] ?? null)
                                    <div class="chart-title">{{ $element['config']['title'] }}</div>
                                @endif
                                <div class="chart-image">
                                    @if(isset($element['config']['chart_image']))
                                        <img src="{{ $element['config']['chart_image'] }}" alt="Chart">
                                    @else
                                        Chart: {{ implode(', ', $element['config']['metric_keys'] ?? []) }}
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('table')
                            <div class="element-content">
                                @if($element['config']['title'] ?? null)
                                    <div class="chart-title">{{ $element['config']['title'] }}</div>
                                @endif
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            @foreach($element['config']['columns'] ?? [] as $column)
                                                <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['tables'][$element['id']] ?? [] as $row)
                                            <tr>
                                                @foreach($element['config']['columns'] ?? [] as $column)
                                                    <td>{{ $row[$column] ?? '--' }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($element['config']['columns'] ?? []) }}" style="text-align: center; color: #9ca3af;">
                                                    No data available
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @case('ai_text')
                            <div class="element-content ai-text">
                                <div class="ai-badge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    AI Generated
                                </div>
                                {{ $element['config']['generated_content'] ?? 'AI content will appear here.' }}
                            </div>
                            @break

                        @case('image')
                            @if($element['config']['src'] ?? null)
                                <img src="{{ $element['config']['src'] }}" alt="{{ $element['config']['alt'] ?? '' }}" style="max-width: 100%; max-height: 100%; object-fit: {{ $element['config']['fit'] ?? 'contain' }};">
                            @endif
                            @break

                        @default
                            <div class="element-content" style="display: flex; align-items: center; justify-content: center; background: #f9fafb; color: #9ca3af;">
                                {{ $element['type'] }}
                            </div>
                    @endswitch
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Generated by Pulse &bull;
        @if($report->is_live)
            Live Data
        @else
            Snapshot from {{ $report->updated_at->format('F j, Y') }}
        @endif
        &bull; <span class="page-number"></span>
    </div>
</body>
</html>
