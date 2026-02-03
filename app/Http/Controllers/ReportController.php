<?php

namespace App\Http\Controllers;

use App\Models\CustomReport;
use App\Models\Participant;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Display list of reports.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $effectiveOrgId = $user->effective_org_id;

        // Build query for reports
        $query = CustomReport::query();

        // If user is a consultant/admin at section level, they can see reports from child orgs
        if ($user->isAdmin() && $user->organization) {
            // Get all accessible org IDs (includes own org and all descendants)
            $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
            $query->whereIn('org_id', $accessibleOrgIds);

            // Filter by specific org if requested
            if ($request->has('org_filter') && in_array($request->org_filter, $accessibleOrgIds)) {
                $query->where('org_id', $request->org_filter);
            }
        } else {
            // Regular users only see their effective org's reports
            $query->where('org_id', $effectiveOrgId);
        }

        $reports = $query->with('organization')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        // Get accessible orgs for filter dropdown (if admin)
        $accessibleOrgs = $user->isAdmin() ? $user->getAccessibleOrganizations() : collect();

        return view('reports.index', compact('reports', 'accessibleOrgs'));
    }

    /**
     * Show report creation page.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Get available templates
        $templates = $this->getTemplates();

        return view('reports.builder', [
            'report' => null,
            'templates' => $templates,
            'mode' => 'create',
        ]);
    }

    /**
     * Show report editor.
     */
    public function edit(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        // Ensure user can access this report (own org or accessible child org)
        if (! $user->canAccessOrganization($report->org_id)) {
            abort(403);
        }

        return view('reports.builder', [
            'report' => $report,
            'templates' => $this->getTemplates(),
            'mode' => 'edit',
        ]);
    }

    /**
     * Duplicate a report.
     */
    public function duplicate(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        if (! $user->canAccessOrganization($report->org_id)) {
            abort(403);
        }

        $newReport = $report->duplicate($user->id);

        return redirect()->route('reports.edit', $newReport)
            ->with('success', 'Report duplicated successfully.');
    }

    /**
     * Push a report to one or more child organizations.
     */
    public function push(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        // Only allow push from user's own org
        if ($report->org_id !== $user->org_id && $report->org_id !== $user->effective_org_id) {
            abort(403);
        }

        $validated = $request->validate([
            'target_org_ids' => 'required|array|min:1',
            'target_org_ids.*' => 'required|integer|exists:organizations,id',
        ]);

        $sourceOrg = $report->organization;
        $pushed = [];
        $errors = [];

        foreach ($validated['target_org_ids'] as $targetOrgId) {
            $targetOrg = \App\Models\Organization::find($targetOrgId);

            // Verify the source org can push to target org
            if (! $sourceOrg->canPushContentTo($targetOrg)) {
                $errors[] = "Cannot push to {$targetOrg->org_name} - not a child organization.";

                continue;
            }

            $newReport = $report->pushToOrganization($targetOrg, $user->id);
            $pushed[] = [
                'org_id' => $targetOrg->id,
                'org_name' => $targetOrg->org_name,
                'report_id' => $newReport->id,
            ];
        }

        return response()->json([
            'success' => count($pushed) > 0,
            'pushed' => $pushed,
            'errors' => $errors,
            'message' => count($pushed).' report(s) pushed successfully.',
        ]);
    }

    /**
     * Generate PDF export.
     */
    public function pdf(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        if ($report->org_id !== $user->org_id) {
            abort(403);
        }

        $pdfService = app(ReportPdfService::class);

        return $pdfService->download($report);
    }

    /**
     * Publish a report (make it publicly accessible).
     */
    public function publish(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        if ($report->org_id !== $user->org_id) {
            abort(403);
        }

        $report->publish();

        return response()->json([
            'success' => true,
            'public_url' => $report->getPublicUrl(),
            'embed_code' => $report->getEmbedCode(),
        ]);
    }

    /**
     * Delete a report.
     */
    public function destroy(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        if ($report->org_id !== $user->org_id) {
            abort(403);
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    /**
     * Preview a report (requires auth, works for drafts).
     */
    public function preview(Request $request, CustomReport $report)
    {
        $user = auth()->user();

        // Check if user has access to the report's organization
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
        if (!in_array($report->org_id, $accessibleOrgIds)) {
            abort(403);
        }

        // Get data based on is_live setting
        $data = $report->is_live
            ? $this->resolveReportData($report)
            : ($report->snapshot_data ?? []);

        return view('reports.public', [
            'report' => $report,
            'data' => $data,
            'branding' => $report->getEffectiveBranding(),
            'isPreview' => true,
        ]);
    }

    /**
     * Public view of a published report (no auth required).
     */
    public function publicView(string $token)
    {
        $report = CustomReport::where('public_token', $token)
            ->where('status', CustomReport::STATUS_PUBLISHED)
            ->firstOrFail();

        // Get data based on is_live setting
        $data = $report->is_live
            ? $this->resolveReportData($report)
            : ($report->snapshot_data ?? []);

        return view('reports.public', [
            'report' => $report,
            'data' => $data,
            'branding' => $report->getEffectiveBranding(),
            'isPreview' => false,
        ]);
    }

    /**
     * Get available report templates.
     */
    protected function getTemplates(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            // Participant Reports
            [
                'id' => 'learner_progress',
                'name' => $terminology->get('report_template_participant_progress_name'),
                'description' => $terminology->get('report_template_participant_progress_description'),
                'thumbnail' => '/images/templates/participant-progress.png',
                'type' => CustomReport::TYPE_STUDENT_PROGRESS,
                'category' => 'participant',
                'layout' => $this->getLearnerProgressLayout(),
            ],
            [
                'id' => 'learner_quick_view',
                'name' => $terminology->get('report_template_participant_quick_view_name'),
                'description' => $terminology->get('report_template_participant_quick_view_description'),
                'thumbnail' => '/images/templates/participant-quick.png',
                'type' => CustomReport::TYPE_STUDENT_PROGRESS,
                'category' => 'participant',
                'layout' => $this->getLearnerQuickViewLayout(),
            ],
            // Cohort Reports
            [
                'id' => 'cohort_summary',
                'name' => $terminology->get('report_template_cohort_summary_name'),
                'description' => $terminology->get('report_template_cohort_summary_description'),
                'thumbnail' => '/images/templates/cohort-summary.png',
                'type' => CustomReport::TYPE_COHORT_SUMMARY,
                'category' => 'cohort',
                'layout' => $this->getCohortSummaryLayout(),
            ],
            [
                'id' => 'grade_level_overview',
                'name' => $terminology->get('report_template_level_overview_name'),
                'description' => $terminology->get('report_template_level_overview_description'),
                'thumbnail' => '/images/templates/level-overview.png',
                'type' => CustomReport::TYPE_COHORT_SUMMARY,
                'category' => 'cohort',
                'layout' => $this->getCohortSummaryLayout(),
            ],
            // Organization Dashboards
            [
                'id' => 'organization_dashboard',
                'name' => $terminology->get('report_template_organization_dashboard_name'),
                'description' => $terminology->get('report_template_organization_dashboard_description'),
                'thumbnail' => '/images/templates/organization-dashboard.png',
                'type' => CustomReport::TYPE_SCHOOL_DASHBOARD,
                'category' => 'organization',
                'layout' => $this->getOrganizationDashboardLayout(),
            ],
            [
                'id' => 'wellness_overview',
                'name' => $terminology->get('report_template_wellness_overview_name'),
                'description' => $terminology->get('report_template_wellness_overview_description'),
                'thumbnail' => '/images/templates/wellness-overview.png',
                'type' => CustomReport::TYPE_SCHOOL_DASHBOARD,
                'category' => 'organization',
                'layout' => $this->getOrganizationDashboardLayout(),
            ],
            // Custom
            [
                'id' => 'blank',
                'name' => $terminology->get('report_template_blank_name'),
                'description' => $terminology->get('report_template_blank_description'),
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'custom',
                'layout' => [],
            ],
        ];
    }

    /**
     * Get Participant Quick View template layout - simplified one-pager.
     */
    protected function getLearnerQuickViewLayout(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 50],
                'config' => [
                    'content' => '<h2 style="margin: 0;">'.$terminology->get('report_template_participant_quick_view_name').'</h2>',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'padding' => 8,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 40, 'y' => 100],
                'size' => ['width' => 350, 'height' => 100],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => $terminology->get('current_label').' '.$terminology->get('metric_gpa_label'),
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#F0F9FF',
                    'borderRadius' => 12,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 410, 'y' => 100],
                'size' => ['width' => 350, 'height' => 100],
                'config' => [
                    'metric_key' => 'attendance_rate',
                    'label' => $terminology->get('metric_attendance_rate_label'),
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#F0FDF4',
                    'borderRadius' => 12,
                    'padding' => 16,
                ],
            ],
        ];
    }

    /**
     * Get Participant Progress template layout.
     */
    protected function getLearnerProgressLayout(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>'.$terminology->get('report_template_participant_progress_name').'</h1>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'padding' => 0,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 40, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => $terminology->get('current_label').' '.$terminology->get('metric_gpa_label'),
                    'show_trend' => true,
                    'comparison_period' => 'last_quarter',
                ],
                'styles' => [
                    'backgroundColor' => '#F0F9FF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 220, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'metric_key' => 'attendance_rate',
                    'label' => $terminology->get('metric_attendance_rate_label'),
                    'show_trend' => true,
                    'format' => 'percentage',
                ],
                'styles' => [
                    'backgroundColor' => '#F0FDF4',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 400, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'metric_key' => 'wellness_score',
                    'label' => $terminology->get('metric_health_wellness_label'),
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#FDF4FF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 580, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'metric_key' => 'engagement_score',
                    'label' => $terminology->get('metric_engagement_label'),
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#FFF7ED',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 40, 'y' => 240],
                'size' => ['width' => 710, 'height' => 300],
                'config' => [
                    'chart_type' => 'line',
                    'title' => $terminology->get('performance_trends_label'),
                    'metric_keys' => ['gpa', 'wellness_score', 'engagement_score'],
                    'colors' => ['#3B82F6', '#10B981', '#F59E0B'],
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'ai_text',
                'position' => ['x' => 40, 'y' => 560],
                'size' => ['width' => 710, 'height' => 180],
                'config' => [
                    'prompt' => 'Write a brief progress summary for this participant based on their metrics.',
                    'format' => 'narrative',
                    'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'],
                ],
                'styles' => [
                    'backgroundColor' => '#F9FAFB',
                    'borderRadius' => 8,
                    'padding' => 20,
                ],
            ],
        ];
    }

    /**
     * Get Cohort Summary template layout.
     */
    protected function getCohortSummaryLayout(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>'.$terminology->get('cohort_summary_report_label').'</h1>',
                    'format' => 'html',
                ],
                'styles' => [],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'table',
                'position' => ['x' => 40, 'y' => 120],
                'size' => ['width' => 710, 'height' => 300],
                'config' => [
                    'title' => $terminology->get('participant_overview_label'),
                    'columns' => ['name', 'gpa', 'attendance', 'risk_level'],
                    'sortable' => true,
                    'data_source' => 'participants',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 40, 'y' => 440],
                'size' => ['width' => 350, 'height' => 250],
                'config' => [
                    'chart_type' => 'pie',
                    'title' => $terminology->get('risk_distribution_label'),
                    'data_source' => 'risk_distribution',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 400, 'y' => 440],
                'size' => ['width' => 350, 'height' => 250],
                'config' => [
                    'chart_type' => 'bar',
                    'title' => $terminology->get('average_metrics_by_level_label'),
                    'metric_keys' => ['gpa', 'attendance_rate'],
                    'group_by' => 'level',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
        ];
    }

    /**
     * Get Organization Dashboard template layout.
     */
    protected function getOrganizationDashboardLayout(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>'.$terminology->get('organization_performance_dashboard_label').'</h1>',
                    'format' => 'html',
                ],
                'styles' => [],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 40, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'label' => $terminology->get('total_participants_label'),
                    'data_source' => 'learner_count',
                    'show_trend' => false,
                ],
                'styles' => [
                    'backgroundColor' => '#EFF6FF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 220, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'label' => $terminology->get('participants_good_standing_label'),
                    'data_source' => 'good_standing_count',
                    'format' => 'percentage',
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#ECFDF5',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 400, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'label' => $terminology->get('at_risk_participants_label'),
                    'data_source' => 'at_risk_count',
                    'format' => 'percentage',
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#FEF3C7',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 580, 'y' => 120],
                'size' => ['width' => 170, 'height' => 100],
                'config' => [
                    'label' => $terminology->get('avg_gpa_label'),
                    'data_source' => 'average_gpa',
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#F5F3FF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 40, 'y' => 240],
                'size' => ['width' => 450, 'height' => 280],
                'config' => [
                    'chart_type' => 'line',
                    'title' => $terminology->get('organization_wide_trends_label'),
                    'metric_keys' => ['average_gpa', 'average_attendance', 'average_wellness'],
                    'aggregation' => 'organization',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 500, 'y' => 240],
                'size' => ['width' => 250, 'height' => 280],
                'config' => [
                    'chart_type' => 'doughnut',
                    'title' => $terminology->get('risk_distribution_label'),
                    'data_source' => 'risk_distribution',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'ai_text',
                'position' => ['x' => 40, 'y' => 540],
                'size' => ['width' => 710, 'height' => 150],
                'config' => [
                    'prompt' => 'Provide an executive summary of organization performance with key insights and recommendations.',
                    'format' => 'executive_summary',
                ],
                'styles' => [
                    'backgroundColor' => '#F9FAFB',
                    'borderRadius' => 8,
                    'padding' => 20,
                ],
            ],
        ];
    }

    /**
     * Resolve report data based on configuration.
     */
    protected function resolveReportData(CustomReport $report): array
    {
        // TODO: Implement ReportDataService
        // For now, return placeholder data
        return [
            'generated_at' => now()->toISOString(),
            'metrics' => [],
        ];
    }
}
