<?php

namespace App\Livewire;

use App\Models\Learner;
use App\Services\ContactMetricService;
use Carbon\Carbon;
use Livewire\Component;

class ContactOverviewCharts extends Component
{
    public string $contactType;

    public int $contactId;

    public string $dateRange = '12_months';

    public array $selectedMetrics = ['gpa', 'wellness_score', 'emotional_wellbeing', 'engagement_score', 'plan_progress'];

    public array $availableMetrics = [];

    protected ContactMetricService $metricService;

    public function boot(ContactMetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
        $this->loadAvailableMetrics();
    }

    protected function loadAvailableMetrics()
    {
        $type = $this->contactType === Learner::class ? 'learner' : 'teacher';

        $this->availableMetrics = match ($type) {
            'learner' => [
                ['key' => 'gpa', 'label' => 'GPA', 'color' => '#3b82f6'],
                ['key' => 'wellness_score', 'label' => 'Health & Wellness', 'color' => '#22c55e'],
                ['key' => 'emotional_wellbeing', 'label' => 'Emotional Well-Being', 'color' => '#a855f7'],
                ['key' => 'engagement_score', 'label' => 'Engagement', 'color' => '#f59e0b'],
                ['key' => 'plan_progress', 'label' => 'Plan Progress', 'color' => '#06b6d4'],
                ['key' => 'attendance_rate', 'label' => 'Attendance Rate', 'color' => '#ec4899'],
            ],
            'teacher' => [
                ['key' => 'classroom_performance', 'label' => 'Classroom Performance', 'color' => '#3b82f6'],
                ['key' => 'learner_growth', 'label' => 'Learner Growth', 'color' => '#22c55e'],
                ['key' => 'pd_progress', 'label' => 'PD Progress', 'color' => '#a855f7'],
            ],
            default => [],
        };
    }

    public function toggleMetric(string $metricKey)
    {
        if (in_array($metricKey, $this->selectedMetrics)) {
            $this->selectedMetrics = array_values(array_diff($this->selectedMetrics, [$metricKey]));
        } else {
            $this->selectedMetrics[] = $metricKey;
        }
    }

    public function setDateRange(string $range)
    {
        $this->dateRange = $range;
    }

    public function getChartDataProperty()
    {
        [$startDate, $endDate, $groupBy] = match ($this->dateRange) {
            '3_months' => [Carbon::now()->subMonths(3), Carbon::now(), 'week'],
            '6_months' => [Carbon::now()->subMonths(6), Carbon::now(), 'week'],
            '12_months' => [Carbon::now()->subMonths(12), Carbon::now(), 'month'],
            '2_years' => [Carbon::now()->subYears(2), Carbon::now(), 'month'],
            'all' => [Carbon::now()->subYears(10), Carbon::now(), 'quarter'],
            default => [Carbon::now()->subMonths(12), Carbon::now(), 'month'],
        };

        return $this->metricService->getChartData(
            $this->contactType,
            $this->contactId,
            $this->selectedMetrics,
            $startDate,
            $endDate,
            $groupBy
        );
    }

    public function render()
    {
        return view('livewire.contact-overview-charts', [
            'chartData' => $this->chartData,
        ]);
    }
}
