<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_name }}</title>
    <style>
        @page {
            margin: {{ $report->getPageSettings()['margins']['top'] ?? 40 }}px {{ $report->getPageSettings()['margins']['right'] ?? 40 }}px {{ $report->getPageSettings()['margins']['bottom'] ?? 60 }}px {{ $report->getPageSettings()['margins']['left'] ?? 40 }}px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
            background: white;
        }

        .header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid {{ $branding['primary_color'] ?? '#3B82F6' }};
        }

        .header-table {
            width: 100%;
        }

        .header-logo {
            height: 35px;
        }

        .header-text {
            text-align: right;
            font-size: 10px;
            color: #6b7280;
        }

        .header-org {
            font-size: 13px;
            font-weight: bold;
            color: {{ $branding['primary_color'] ?? '#3B82F6' }};
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
        }

        .report-description {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 25px;
        }

        /* Grid container for metric cards */
        .metrics-row {
            margin-bottom: 20px;
        }

        .metrics-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .metric-card {
            background: #F0F9FF;
            border-radius: 6px;
            padding: 12px 15px;
            vertical-align: top;
        }

        .metric-card.green { background: #F0FDF4; }
        .metric-card.purple { background: #FDF4FF; }
        .metric-card.orange { background: #FFF7ED; }
        .metric-card.yellow { background: #FEF3C7; }

        .metric-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        .metric-trend {
            font-size: 9px;
            margin-top: 3px;
        }

        .metric-trend.positive { color: #10b981; }
        .metric-trend.negative { color: #ef4444; }

        /* Content blocks */
        .content-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .content-block.bordered {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            background: #ffffff;
        }

        .block-title {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }

        /* Text elements */
        .text-element h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #111827;
        }

        .text-element h2 {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #1f2937;
        }

        .text-element h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #374151;
        }

        .text-element p {
            margin-bottom: 8px;
            color: #4b5563;
        }

        .text-element ul, .text-element ol {
            margin-left: 20px;
            margin-bottom: 8px;
        }

        /* Charts placeholder */
        .chart-container {
            background: #f9fafb;
            border-radius: 6px;
            padding: 15px;
            min-height: 180px;
            text-align: center;
        }

        .chart-placeholder {
            color: #9ca3af;
            font-size: 10px;
            padding: 30px;
        }

        .chart-image {
            max-width: 100%;
            max-height: 250px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .data-table th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            color: #374151;
        }

        .data-table td {
            border-bottom: 1px solid #f3f4f6;
            padding: 8px 10px;
            color: #4b5563;
        }

        .data-table tr:nth-child(even) td {
            background: #fafafa;
        }

        /* AI Text */
        .ai-text-block {
            background: #f9fafb;
            border-radius: 6px;
            padding: 15px 18px;
        }

        .ai-badge {
            font-size: 8px;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .ai-content {
            font-size: 11px;
            color: #374151;
            line-height: 1.7;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .page-number:before {
            content: "Page " counter(page);
        }

        /* Image elements */
        .image-element {
            text-align: center;
        }

        .image-element img {
            max-width: 100%;
            max-height: 200px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    @if($branding['logo_path'] ?? null)
                        <img src="{{ $branding['logo_path'] }}" alt="Logo" class="header-logo">
                    @else
                        <span style="font-size: 16px; font-weight: bold; color: {{ $branding['primary_color'] ?? '#3B82F6' }}">
                            {{ $report->organization->name ?? 'Pulse' }}
                        </span>
                    @endif
                </td>
                <td class="header-text">
                    <div class="header-org">{{ $report->organization->name ?? '' }}</div>
                    <div>Generated: {{ now()->format('F j, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Title -->
    <h1 class="report-title">{{ $report->report_name }}</h1>
    @if($report->report_description)
        <p class="report-description">{{ $report->report_description }}</p>
    @endif

    <!-- Elements sorted by vertical position -->
    @php
        // Sort elements by Y position for proper flow
        $sortedElements = collect($elements)->sortBy(fn($el) => $el['position']['y'] ?? 0)->values();

        // Group metric cards that are on the same row (similar Y position)
        $metricCards = $sortedElements->filter(fn($el) => ($el['type'] ?? '') === 'metric_card');
        $otherElements = $sortedElements->filter(fn($el) => ($el['type'] ?? '') !== 'metric_card');

        // Group metric cards by approximate Y position (within 20px)
        $metricCardGroups = [];
        $currentGroup = [];
        $currentY = null;

        foreach ($metricCards as $card) {
            $y = $card['position']['y'] ?? 0;
            if ($currentY === null || abs($y - $currentY) <= 30) {
                $currentGroup[] = $card;
                if ($currentY === null) $currentY = $y;
            } else {
                if (!empty($currentGroup)) {
                    $metricCardGroups[] = ['y' => $currentY, 'cards' => $currentGroup];
                }
                $currentGroup = [$card];
                $currentY = $y;
            }
        }
        if (!empty($currentGroup)) {
            $metricCardGroups[] = ['y' => $currentY, 'cards' => $currentGroup];
        }

        // Merge all elements with their Y positions
        $allItems = [];
        foreach ($metricCardGroups as $group) {
            $allItems[] = ['type' => 'metric_group', 'y' => $group['y'], 'cards' => $group['cards']];
        }
        foreach ($otherElements as $el) {
            $allItems[] = ['type' => 'element', 'y' => $el['position']['y'] ?? 0, 'element' => $el];
        }

        // Sort by Y position
        usort($allItems, fn($a, $b) => $a['y'] <=> $b['y']);
    @endphp

    @foreach($allItems as $item)
        @if($item['type'] === 'metric_group')
            <!-- Metric Cards Row -->
            <div class="metrics-row">
                <table class="metrics-table">
                    <tr>
                        @php $colorClasses = ['', 'green', 'purple', 'orange', 'yellow']; @endphp
                        @foreach($item['cards'] as $idx => $card)
                            @php
                                $metricKey = $card['config']['metric_key'] ?? '';
                                $metricData = $data['metrics'][$metricKey] ?? $data['aggregates'][$metricKey] ?? null;
                                $value = $metricData['value'] ?? $metricData['average'] ?? '--';
                                $bgClass = $colorClasses[$idx % count($colorClasses)];
                            @endphp
                            <td class="metric-card {{ $bgClass }}" style="width: {{ 100 / count($item['cards']) }}%;">
                                <div class="metric-label">{{ $card['config']['label'] ?? ucwords(str_replace('_', ' ', $metricKey)) }}</div>
                                <div class="metric-value">{{ is_numeric($value) ? number_format($value, 2) : $value }}</div>
                                @if($card['config']['show_trend'] ?? false)
                                    <div class="metric-trend positive">+0.0%</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </table>
            </div>
        @else
            @php $element = $item['element']; @endphp
            <div class="content-block {{ in_array($element['type'], ['chart', 'table']) ? 'bordered' : '' }}">
                @switch($element['type'])
                    @case('text')
                        <div class="text-element">
                            {!! $element['config']['content'] ?? '' !!}
                        </div>
                        @break

                    @case('chart')
                        @if($element['config']['title'] ?? null)
                            <div class="block-title">{{ $element['config']['title'] }}</div>
                        @endif
                        <div class="chart-container">
                            @if(isset($element['config']['chart_image']))
                                <img src="{{ $element['config']['chart_image'] }}" alt="Chart" class="chart-image">
                            @else
                                <div class="chart-placeholder">
                                    <div style="margin-bottom: 8px;">📊 {{ ucfirst($element['config']['chart_type'] ?? 'Line') }} Chart</div>
                                    <div>Metrics: {{ implode(', ', array_map(fn($k) => ucwords(str_replace('_', ' ', $k)), $element['config']['metric_keys'] ?? [])) }}</div>
                                    <div style="margin-top: 10px; font-size: 9px; color: #b0b0b0;">(Charts render in web view)</div>
                                </div>
                            @endif
                        </div>
                        @break

                    @case('table')
                        @if($element['config']['title'] ?? null)
                            <div class="block-title">{{ $element['config']['title'] }}</div>
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
                                        <td colspan="{{ count($element['config']['columns'] ?? []) }}" style="text-align: center; color: #9ca3af; padding: 20px;">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @break

                    @case('ai_text')
                        <div class="ai-text-block">
                            <div class="ai-badge">✨ AI Generated Insight</div>
                            <div class="ai-content">
                                {{ $element['config']['generated_content'] ?? 'AI content will appear here after generation.' }}
                            </div>
                        </div>
                        @break

                    @case('image')
                        @if($element['config']['src'] ?? null)
                            <div class="image-element">
                                <img src="{{ $element['config']['src'] }}" alt="{{ $element['config']['alt'] ?? '' }}">
                            </div>
                        @endif
                        @break

                    @case('spacer')
                        <div style="height: {{ ($element['size']['height'] ?? 40) / 2 }}px;"></div>
                        @break

                    @default
                        {{-- Skip unknown element types --}}
                @endswitch
            </div>
        @endif
    @endforeach

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
