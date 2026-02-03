<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContactMetric;
use App\Models\Participant;
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
            'contact_type' => 'required|string|in:participant,user,App\\Models\\Participant,App\\Models\\User',
            'contact_id' => 'required|integer',
            'metrics' => 'required|array',
            'metrics.*' => 'string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'group_by' => 'in:day,week,month,quarter',
        ]);

        // Map shorthand contact type to full class name
        $typeMap = [
            'participant' => 'App\\Models\\Participant',
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
            'contact_type' => 'required|string|in:participant,user,App\\Models\\Participant,App\\Models\\User',
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
            'participant' => 'App\\Models\\Participant',
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
     * Get heat map data for a participant.
     */
    public function heatMap(Request $request, Participant $participant): JsonResponse
    {
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        $data = $this->metricService->getHeatMapData(
            $participant,
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
        $contactType = $request->get('contact_type', 'participant');
        $terminology = app(\App\Services\TerminologyService::class);

        $metrics = match ($contactType) {
            'participant' => [
                ['key' => 'gpa', 'label' => $terminology->get('metric_gpa_label'), 'category' => 'academics'],
                ['key' => 'wellness_score', 'label' => $terminology->get('metric_health_wellness_label'), 'category' => 'wellness'],
                ['key' => 'emotional_wellbeing', 'label' => $terminology->get('metric_emotional_wellbeing_label'), 'category' => 'wellness'],
                ['key' => 'engagement_score', 'label' => $terminology->get('metric_engagement_label'), 'category' => 'engagement'],
                ['key' => 'plan_progress', 'label' => $terminology->get('metric_plan_progress_label'), 'category' => 'academics'],
                ['key' => 'attendance_rate', 'label' => $terminology->get('metric_attendance_rate_label'), 'category' => 'attendance'],
                ['key' => 'behavior_score', 'label' => $terminology->get('metric_behavior_label'), 'category' => 'behavior'],
                ['key' => 'life_skills_score', 'label' => $terminology->get('metric_life_skills_label'), 'category' => 'life_skills'],
            ],
            'instructor' => [
                ['key' => 'learning_group_performance', 'label' => $terminology->get('metric_learning_group_performance_label'), 'category' => 'learning_group'],
                ['key' => 'learner_growth', 'label' => $terminology->get('metric_participant_growth_label'), 'category' => 'learning_group'],
                ['key' => 'pd_progress', 'label' => $terminology->get('metric_pd_progress_label'), 'category' => 'professional_development'],
            ],
            default => [],
        };

        return response()->json(['metrics' => $metrics]);
    }
}
