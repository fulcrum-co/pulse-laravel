<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\Workflow;
use App\Models\WorkflowExecution;

class DashboardWidget extends Model
{
    protected $fillable = [
        'dashboard_id',
        'widget_type',
        'title',
        'config',
        'position',
        'order',
    ];

    protected $casts = [
        'config' => 'array',
        'position' => 'array',
    ];

    /**
     * Get the dashboard this widget belongs to.
     */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /**
     * Get widget data based on type and config.
     */
    public function getData(int $orgId): array
    {
        return match ($this->widget_type) {
            Dashboard::WIDGET_METRIC_CARD => $this->getMetricCardData($orgId),
            Dashboard::WIDGET_BAR_CHART => $this->getBarChartData($orgId),
            Dashboard::WIDGET_LINE_CHART => $this->getLineChartData($orgId),
            Dashboard::WIDGET_STUDENT_LIST => $this->getStudentListData($orgId),
            Dashboard::WIDGET_SURVEY_SUMMARY => $this->getSurveySummaryData($orgId),
            Dashboard::WIDGET_ALERT_FEED => $this->getAlertFeedData($orgId),
            default => [],
        };
    }

    /**
     * Get metric card data.
     */
    protected function getMetricCardData(int $orgId): array
    {
        $dataSource = $this->config['data_source'] ?? 'students_total';
        $color = $this->config['color'] ?? 'blue';

        $value = match ($dataSource) {
            'students_total' => Student::where('org_id', $orgId)->count(),
            'students_at_risk' => Student::where('org_id', $orgId)->where('risk_level', 'high')->count(),
            'students_good' => Student::where('org_id', $orgId)->where('risk_level', 'good')->count(),
            'students_low_risk' => Student::where('org_id', $orgId)->where('risk_level', 'low')->count(),
            'surveys_active' => Survey::where('org_id', $orgId)->where('status', 'active')->count(),
            'surveys_total' => Survey::where('org_id', $orgId)->count(),
            'responses_today' => SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                ->whereDate('completed_at', today())->count(),
            'responses_week' => SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                ->where('completed_at', '>=', now()->startOfWeek())->count(),
            default => 0,
        };

        // Calculate change from last period
        $previousValue = match ($dataSource) {
            'responses_today' => SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                ->whereDate('completed_at', today()->subDay())->count(),
            'responses_week' => SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                ->whereBetween('completed_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count(),
            default => null,
        };

        $change = null;
        if ($previousValue !== null && $previousValue > 0) {
            $change = round((($value - $previousValue) / $previousValue) * 100, 1);
        }

        return [
            'value' => $value,
            'formatted_value' => $this->formatNumber($value),
            'color' => $color,
            'change' => $change,
            'change_direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Get bar chart data.
     */
    protected function getBarChartData(int $orgId): array
    {
        $dataSource = $this->config['data_source'] ?? 'survey_responses_weekly';
        $compare = $this->config['compare'] ?? false;

        $data = [];

        if ($dataSource === 'survey_responses_weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $thisWeek = SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                    ->whereDate('completed_at', $date)->count();

                $item = [
                    'label' => $date->format('M d'),
                    'value' => $thisWeek,
                ];

                if ($compare) {
                    $lastWeek = SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                        ->whereDate('completed_at', $date->copy()->subWeek())->count();
                    $item['compare_value'] = $lastWeek;
                }

                $data[] = $item;
            }
        } elseif ($dataSource === 'risk_distribution') {
            $data = [
                ['label' => 'Good', 'value' => Student::where('org_id', $orgId)->where('risk_level', 'good')->count(), 'color' => 'green'],
                ['label' => 'Low Risk', 'value' => Student::where('org_id', $orgId)->where('risk_level', 'low')->count(), 'color' => 'yellow'],
                ['label' => 'High Risk', 'value' => Student::where('org_id', $orgId)->where('risk_level', 'high')->count(), 'color' => 'red'],
            ];
        }

        return [
            'data' => $data,
            'compare' => $compare,
            'labels' => ['This Week', 'Last Week'],
        ];
    }

    /**
     * Get line chart data.
     */
    protected function getLineChartData(int $orgId): array
    {
        $dataSource = $this->config['data_source'] ?? 'survey_responses_trend';
        $days = $this->config['days'] ?? 30;

        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = SurveyAttempt::whereHas('survey', fn($q) => $q->where('org_id', $orgId))
                ->whereDate('completed_at', $date)->count();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'value' => $value,
            ];
        }

        return ['data' => $data];
    }

    /**
     * Get student list data.
     */
    protected function getStudentListData(int $orgId): array
    {
        $filter = $this->config['filter'] ?? 'all';
        $limit = $this->config['limit'] ?? 10;

        $query = Student::with('user')->where('org_id', $orgId);

        $query = match ($filter) {
            'high_risk' => $query->where('risk_level', 'high'),
            'low_risk' => $query->where('risk_level', 'low'),
            'good' => $query->where('risk_level', 'good'),
            'recent_checkin' => $query->whereHas('user', function ($q) {
                $q->whereHas('surveyAttempts', fn($q2) => $q2->where('completed_at', '>=', now()->subWeek()));
            }),
            default => $query,
        };

        $students = $query->orderBy('risk_score', 'desc')->limit($limit)->get();

        return [
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user->full_name ?? 'Unknown',
                'grade' => $s->grade_level,
                'risk_level' => $s->risk_level,
                'risk_score' => $s->risk_score,
                'avatar_url' => $s->user->avatar_url ?? null,
            ])->toArray(),
            'filter' => $filter,
            'total' => $query->count(),
        ];
    }

    /**
     * Get survey summary data.
     */
    protected function getSurveySummaryData(int $orgId): array
    {
        $surveys = Survey::where('org_id', $orgId)
            ->withCount(['attempts', 'attempts as completed_count' => fn($q) => $q->completed()])
            ->orderBy('created_at', 'desc')
            ->limit($this->config['limit'] ?? 5)
            ->get();

        return [
            'surveys' => $surveys->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'status' => $s->status,
                'attempts_count' => $s->attempts_count,
                'completed_count' => $s->completed_count,
                'completion_rate' => $s->attempts_count > 0
                    ? round(($s->completed_count / $s->attempts_count) * 100)
                    : 0,
            ])->toArray(),
            'total_active' => Survey::where('org_id', $orgId)->where('status', 'active')->count(),
        ];
    }

    /**
     * Get alert feed data.
     */
    protected function getAlertFeedData(int $orgId): array
    {
        // Check if WorkflowExecution model exists
        if (!class_exists(WorkflowExecution::class)) {
            return ['executions' => [], 'message' => 'Workflow system not configured'];
        }

        $executions = WorkflowExecution::whereHas('workflow', fn($q) => $q->where('org_id', $orgId))
            ->with('workflow')
            ->orderBy('started_at', 'desc')
            ->limit($this->config['limit'] ?? 10)
            ->get();

        return [
            'executions' => $executions->map(fn($e) => [
                'id' => $e->id,
                'workflow_name' => $e->workflow->name ?? 'Unknown',
                'status' => $e->status,
                'triggered_by' => $e->triggered_by,
                'started_at' => $e->started_at?->diffForHumans(),
            ])->toArray(),
        ];
    }

    /**
     * Format large numbers for display.
     */
    protected function formatNumber(int $value): string
    {
        if ($value >= 1000000) {
            return round($value / 1000000, 1) . 'M';
        }
        if ($value >= 1000) {
            return round($value / 1000, 1) . 'K';
        }
        return (string) $value;
    }

    /**
     * Get position value with default.
     */
    public function getPositionValue(string $key, $default = 0)
    {
        return $this->position[$key] ?? $default;
    }

    /**
     * Get config value with default.
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
