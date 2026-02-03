<div>
    @php($terminology = app(\App\Services\TerminologyService::class))
    <div class="h-64">
        <canvas id="dashboard-chart"></canvas>
    </div>

    <!-- Legend -->
    <div class="flex items-center justify-center gap-6 mt-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-pulse-purple-600"></div>
            <span class="text-sm text-gray-600">@term('this_week_label')</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-pulse-orange-500"></div>
            <span class="text-sm text-gray-600">@term('last_week_label')</span>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dashboard-chart');
        if (!ctx) return;

        const chartData = @json($chartData);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: @json($terminology->get('this_week_label')),
                        data: chartData.map(d => d.thisWeek),
                        backgroundColor: '#7C3AED',
                        borderRadius: 4,
                        barThickness: 24
                    },
                    {
                        label: @json($terminology->get('last_week_label')),
                        data: chartData.map(d => d.lastWeek),
                        backgroundColor: '#F97316',
                        borderRadius: 4,
                        barThickness: 24
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#E5E7EB',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1
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
    });
</script>
@endpush
