<?php

namespace App\Livewire;

use App\Models\Participant;
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
        $terminology = app(\App\Services\TerminologyService::class);
        $type = $this->contactType === Participant::class ? 'participant' : 'instructor';

        $this->availableMetrics = match ($type) {
            'participant' => [
                ['key' => 'gpa', 'label' => $terminology->get('metric_gpa_label'), 'color' => '#3b82f6'],
                ['key' => 'wellness_score', 'label' => $terminology->get('metric_health_wellness_label'), 'color' => '#22c55e'],
                ['key' => 'emotional_wellbeing', 'label' => $terminology->get('metric_emotional_wellbeing_label'), 'color' => '#a855f7'],
                ['key' => 'engagement_score', 'label' => $terminology->get('metric_engagement_label'), 'color' => '#f59e0b'],
                ['key' => 'plan_progress', 'label' => $terminology->get('metric_plan_progress_label'), 'color' => '#06b6d4'],
                ['key' => 'attendance_rate', 'label' => $terminology->get('metric_attendance_rate_label'), 'color' => '#ec4899'],
            ],
            'instructor' => [
                ['key' => 'learning_group_performance', 'label' => $terminology->get('metric_learning_group_performance_label'), 'color' => '#3b82f6'],
                ['key' => 'learner_growth', 'label' => $terminology->get('metric_participant_growth_label'), 'color' => '#22c55e'],
                ['key' => 'pd_progress', 'label' => $terminology->get('metric_pd_progress_label'), 'color' => '#a855f7'],
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
