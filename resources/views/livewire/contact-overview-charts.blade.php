<div>
    <!-- Date Range Selector -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach(['3_months' => '3 Months', '6_months' => '6 Months', '12_months' => '1 Year', '2_years' => '2 Years', 'all' => 'All Time'] as $range => $label)
            <button
                wire:click="setDateRange('{{ $range }}')"
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $dateRange === $range ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Metric Toggles -->
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($availableMetrics as $metric)
        <button
            wire:click="toggleMetric('{{ $metric['key'] }}')"
            class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors {{ in_array($metric['key'], $selectedMetrics) ? 'border-current bg-opacity-10' : 'border-gray-300 text-gray-500 hover:border-gray-400' }}"
            style="{{ in_array($metric['key'], $selectedMetrics) ? 'color: ' . $metric['color'] . '; background-color: ' . $metric['color'] . '20;' : '' }}"
        >
            <span class="w-3 h-3 rounded-full {{ in_array($metric['key'], $selectedMetrics) ? '' : 'bg-gray-300' }}" style="{{ in_array($metric['key'], $selectedMetrics) ? 'background-color: ' . $metric['color'] : '' }}"></span>
            {{ $metric['label'] }}
        </button>
        @endforeach
    </div>

    <!-- Chart Container -->
    <div class="h-80" wire:ignore>
        <canvas id="overview-chart-{{ $contactId }}"></canvas>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap items-center justify-center gap-6 mt-4">
        @foreach($availableMetrics as $metric)
        @if(in_array($metric['key'], $selectedMetrics))
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full" style="background-color: {{ $metric['color'] }}"></div>
            <span class="text-sm text-gray-600">{{ $metric['label'] }}</span>
        </div>
        @endif
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initOverviewChart();
    });

    document.addEventListener('livewire:navigated', function() {
        initOverviewChart();
    });

    function initOverviewChart() {
        const ctx = document.getElementById('overview-chart-{{ $contactId }}');
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (window.overviewChart_{{ $contactId }}) {
            window.overviewChart_{{ $contactId }}.destroy();
        }

        const chartData = @json($chartData);
        const availableMetrics = @json($availableMetrics);

        const datasets = [];
        availableMetrics.forEach(metric => {
            if (chartData[metric.key]) {
                datasets.push({
                    label: metric.label,
                    data: chartData[metric.key].map(d => d.value),
                    borderColor: metric.color,
                    backgroundColor: metric.color + '20',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6
                });
            }
        });

        // Get labels from the first available metric
        const firstMetric = Object.keys(chartData)[0];
        const labels = firstMetric ? chartData[firstMetric].map(d => d.period) : [];

        window.overviewChart_{{ $contactId }} = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: '#E5E7EB',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    // Update chart when Livewire updates
    Livewire.on('chartUpdated', () => {
        initOverviewChart();
    });
</script>
@endpush
