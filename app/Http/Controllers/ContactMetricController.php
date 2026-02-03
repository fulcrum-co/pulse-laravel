<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContactMetric;
use App\Models\Learner;
use App\Services\ContactMetricService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMetricController extends Controller
{
    public function __construct(
        protected ContactMetricService $metricService
    ) {}

    /**
     * Get time-series data for charts.
     */
    public function timeSeries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:learner,user,App\\Models\\Learner,App\\Models\\User',
            'contact_id' => 'required|integer',
            'metrics' => 'required|array',
            'metrics.*' => 'string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'group_by' => 'in:day,week,month,quarter',
        ]);

        // Map shorthand contact type to full class name
        $typeMap = [
            'learner' => 'App\\Models\\Learner',
            'user' => 'App\\Models\\User',
        ];
        $contactType = $typeMap[$validated['contact_type']] ?? $validated['contact_type'];

        $data = $this->metricService->getChartData(
            $contactType,
            $validated['contact_id'],
            $validated['metrics'],
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            $validated['group_by'] ?? 'week'
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Manually add a metric.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:learner,user,App\\Models\\Learner,App\\Models\\User',
            'contact_id' => 'required|integer',
            'metric_category' => 'required|string',
            'metric_key' => 'required|string',
            'numeric_value' => 'nullable|numeric',
            'text_value' => 'nullable|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'organization_year' => 'nullable|string',
            'quarter' => 'nullable|integer|between:1,4',
        ]);

        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'learner' => 'App\\Models\\Learner',
            'user' => 'App\\Models\\User',
        ];
        $contactType = $typeMap[$validated['contact_type']] ?? $validated['contact_type'];

        $metric = $this->metricService->ingestMetric([
            'org_id' => $user->org_id,
            'contact_type' => $contactType,
            'contact_id' => $validated['contact_id'],
            'metric_category' => $validated['metric_category'],
            'metric_key' => $validated['metric_key'],
            'numeric_value' => $validated['numeric_value'],
            'text_value' => $validated['text_value'],
            'source_type' => ContactMetric::SOURCE_MANUAL,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'period_type' => 'point_in_time',
            'organization_year' => $validated['organization_year'] ?? $this->metricService->getCurrentOrganizationYear(),
            'quarter' => $validated['quarter'] ?? $this->metricService->getCurrentQuarter(),
            'recorded_by_user_id' => $user->id,
            'recorded_at' => now(),
        ]);

        AuditLog::log('create', $metric);

        return response()->json([
            'success' => true,
            'metric' => $metric,
        ], 201);
    }

    /**
     * Get heat map data for a learner.
     */
    public function heatMap(Request $request, Learner $learner): JsonResponse
    {
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        $data = $this->metricService->getHeatMapData(
            $learner,
            $organizationYear,
            ['academics', 'attendance', 'behavior', 'life_skills']
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Get available metrics for a contact type.
     */
    public function available(Request $request): JsonResponse
    {
        $contactType = $request->get('contact_type', 'learner');

        $metrics = match ($contactType) {
            'learner' => [
                ['key' => 'gpa', 'label' => 'GPA', 'category' => 'academics'],
                ['key' => 'wellness_score', 'label' => 'Health & Wellness', 'category' => 'wellness'],
                ['key' => 'emotional_wellbeing', 'label' => 'Emotional Well-Being', 'category' => 'wellness'],
                ['key' => 'engagement_score', 'label' => 'Engagement', 'category' => 'engagement'],
                ['key' => 'plan_progress', 'label' => 'Plan Progress', 'category' => 'academics'],
                ['key' => 'attendance_rate', 'label' => 'Attendance Rate', 'category' => 'attendance'],
                ['key' => 'behavior_score', 'label' => 'Behavior', 'category' => 'behavior'],
                ['key' => 'life_skills_score', 'label' => 'Life Skills', 'category' => 'life_skills'],
            ],
            'teacher' => [
                ['key' => 'classroom_performance', 'label' => 'Classroom Performance', 'category' => 'classroom'],
                ['key' => 'learner_growth', 'label' => 'Learner Growth', 'category' => 'classroom'],
                ['key' => 'pd_progress', 'label' => 'PD Progress', 'category' => 'professional_development'],
            ],
            default => [],
        };

        return response()->json(['metrics' => $metrics]);
    }
}
