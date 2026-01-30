<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->report_name }} - Pulse</title>

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "v99lylydfx");
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '{{ $branding['primary_color'] ?? '#3B82F6' }}',
                            secondary: '{{ $branding['secondary_color'] ?? '#1E40AF' }}',
                        }
                    },
                    fontFamily: {
                        sans: ['{{ $branding['font_family'] ?? 'Inter, sans-serif' }}'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: {{ $branding['font_family'] ?? 'Inter, sans-serif' }}; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($branding['logo_path'] ?? null)
                    <img src="{{ $branding['logo_path'] }}" alt="Logo" class="h-10">
                @else
                    <span class="text-xl font-bold" style="color: {{ $branding['primary_color'] ?? '#3B82F6' }}">
                        {{ $report->organization->name ?? 'Pulse' }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                @if($report->is_live)
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        Live Data
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        Snapshot: {{ $report->updated_at->format('M j, Y') }}
                    </span>
                @endif
            </div>
        </div>
    </header>

    <!-- Report Content -->
    <main class="max-w-6xl mx-auto py-8 px-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $report->report_name }}</h1>
            @if($report->report_description)
                <p class="text-gray-600">{{ $report->report_description }}</p>
            @endif
        </div>

        <!-- Render Elements -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 relative" style="min-height: 800px;">
            @foreach($report->report_layout ?? [] as $element)
                <div
                    class="absolute"
                    style="
                        left: {{ $element['position']['x'] ?? 0 }}px;
                        top: {{ $element['position']['y'] ?? 0 }}px;
                        width: {{ $element['size']['width'] ?? 200 }}px;
                        height: {{ $element['size']['height'] ?? 100 }}px;
                        background-color: {{ $element['styles']['backgroundColor'] ?? 'transparent' }};
                        border-radius: {{ $element['styles']['borderRadius'] ?? 0 }}px;
                        padding: {{ $element['styles']['padding'] ?? 0 }}px;
                        @if(isset($element['styles']['borderWidth']) && $element['styles']['borderWidth'] > 0)
                            border: {{ $element['styles']['borderWidth'] }}px solid {{ $element['styles']['borderColor'] ?? '#E5E7EB' }};
                        @endif
                    "
                >
                    @switch($element['type'])
                        @case('text')
                            <div class="prose prose-sm max-w-none">
                                {!! $element['config']['content'] ?? '' !!}
                            </div>
                            @break

                        @case('chart')
                            <div class="w-full h-full flex flex-col">
                                @if(isset($element['config']['title']))
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $element['config']['title'] }}</h4>
                                @endif
                                <div class="flex-1">
                                    <canvas id="chart-{{ $loop->index }}"></canvas>
                                </div>
                            </div>
                            @break

                        @case('metric_card')
                            @php
                                $metricKey = $element['config']['metric_key'] ?? '';
                                $metricValue = $data['metrics'][$metricKey]['value']
                                    ?? $data['aggregates'][$metricKey]['average']
                                    ?? null;
                                $formattedValue = $metricValue !== null ? number_format($metricValue, 2) : '--';
                            @endphp
                            <div class="w-full h-full flex flex-col justify-center">
                                <span class="text-xs text-gray-500 uppercase tracking-wider">{{ $element['config']['label'] ?? ucwords(str_replace('_', ' ', $metricKey)) }}</span>
                                <span class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ $formattedValue }}
                                </span>
                                @if($element['config']['show_trend'] ?? false)
                                    <span class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                        </svg>
                                        Trend
                                    </span>
                                @endif
                            </div>
                            @break

                        @case('ai_text')
                            <div class="w-full h-full">
                                @if($element['config']['generated_content'] ?? null)
                                    <p class="text-sm text-gray-700">{{ $element['config']['generated_content'] }}</p>
                                @else
                                    <p class="text-sm text-gray-400 italic">AI content will appear here.</p>
                                @endif
                            </div>
                            @break

                        @case('table')
                            <div class="w-full h-full overflow-auto">
                                @if(isset($element['config']['title']))
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $element['config']['title'] }}</h4>
                                @endif
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            @foreach($element['config']['columns'] ?? [] as $column)
                                                <th class="text-left py-2 px-3 font-medium text-gray-600">{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-b border-gray-100">
                                            @foreach($element['config']['columns'] ?? [] as $column)
                                                <td class="py-2 px-3 text-gray-700">--</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @case('image')
                            @if($element['config']['src'] ?? null)
                                <img src="{{ $element['config']['src'] }}" alt="{{ $element['config']['alt'] ?? '' }}" class="w-full h-full object-{{ $element['config']['fit'] ?? 'contain' }}">
                            @endif
                            @break

                        @default
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded">
                                <span class="text-sm text-gray-400">{{ $element['type'] }}</span>
                            </div>
                    @endswitch
                </div>
            @endforeach
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-6xl mx-auto px-6 text-center text-sm text-gray-500">
            Generated by <a href="https://pulse.app" class="text-brand-primary hover:underline">Pulse</a>
            @if($report->is_live)
                &bull; Updated in real-time
            @else
                &bull; Snapshot from {{ $report->updated_at->format('F j, Y') }}
            @endif
        </div>
    </footer>

    <script>
        // Chart data from server
        const chartData = @json($data['charts'] ?? []);

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($report->report_layout ?? [] as $element)
                @if($element['type'] === 'chart')
                    (function() {
                        const ctx = document.getElementById('chart-{{ $loop->index }}');
                        if (!ctx) return;

                        const metricKeys = @json($element['config']['metric_keys'] ?? []);
                        const chartType = '{{ $element['config']['chart_type'] ?? 'line' }}';
                        const colors = @json($element['config']['colors'] ?? ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']);

                        // Build datasets from chart data
                        const datasets = [];
                        const labels = [];

                        metricKeys.forEach((key, index) => {
                            const data = chartData[key] || [];
                            if (data.length > 0 && labels.length === 0) {
                                data.forEach(d => labels.push(d.period));
                            }
                            datasets.push({
                                label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                                data: data.map(d => d.value),
                                borderColor: colors[index % colors.length],
                                backgroundColor: colors[index % colors.length] + '20',
                                tension: 0.3,
                                fill: chartType === 'line'
                            });
                        });

                        // If no data, show placeholder
                        if (labels.length === 0) {
                            labels.push('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun');
                            datasets.push({
                                label: '{{ $element['config']['title'] ?? 'No Data' }}',
                                data: [null, null, null, null, null, null],
                                borderColor: '#CBD5E1',
                                tension: 0.3
                            });
                        }

                        new Chart(ctx, {
                            type: chartType,
                            data: { labels, datasets },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: metricKeys.length > 1,
                                        position: 'bottom'
                                    }
                                },
                                scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
                                    y: { beginAtZero: false },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    })();
                @endif
            @endforeach
        });
    </script>
</body>
</html>
