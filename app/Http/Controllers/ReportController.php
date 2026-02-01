<?php

namespace App\Http\Controllers;

use App\Models\CustomReport;
use App\Models\Student;
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

        // If user is a consultant/admin at district level, they can see reports from child orgs
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

        if ($report->org_id !== $user->org_id) {
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
        return [
            [
                'id' => 'student_progress',
                'name' => 'Student Progress Report',
                'description' => 'Individual student performance tracking with metrics over time',
                'thumbnail' => '/images/templates/student-progress.png',
                'type' => CustomReport::TYPE_STUDENT_PROGRESS,
                'layout' => $this->getStudentProgressLayout(),
            ],
            [
                'id' => 'cohort_summary',
                'name' => 'Cohort Summary',
                'description' => 'Aggregate metrics for a group of students',
                'thumbnail' => '/images/templates/cohort-summary.png',
                'type' => CustomReport::TYPE_COHORT_SUMMARY,
                'layout' => $this->getCohortSummaryLayout(),
            ],
            [
                'id' => 'school_dashboard',
                'name' => 'School Dashboard',
                'description' => 'School-wide analytics and KPIs',
                'thumbnail' => '/images/templates/school-dashboard.png',
                'type' => CustomReport::TYPE_SCHOOL_DASHBOARD,
                'layout' => $this->getSchoolDashboardLayout(),
            ],
            [
                'id' => 'blank',
                'name' => 'Blank Canvas',
                'description' => 'Start from scratch with a blank report',
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'layout' => [],
            ],
        ];
    }

    /**
     * Get Student Progress template layout.
     */
    protected function getStudentProgressLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>Student Progress Report</h1>',
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
                    'label' => 'Current GPA',
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
                    'label' => 'Attendance',
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
                    'label' => 'Wellness',
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
                    'label' => 'Engagement',
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
                    'title' => 'Performance Trends',
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
                    'prompt' => 'Write a brief progress summary for this student based on their metrics.',
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
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>Cohort Summary Report</h1>',
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
                    'title' => 'Student Overview',
                    'columns' => ['name', 'gpa', 'attendance', 'risk_level'],
                    'sortable' => true,
                    'data_source' => 'students',
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
                    'title' => 'Risk Distribution',
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
                    'title' => 'Average Metrics by Grade',
                    'metric_keys' => ['gpa', 'attendance_rate'],
                    'group_by' => 'grade_level',
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
     * Get School Dashboard template layout.
     */
    protected function getSchoolDashboardLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 40, 'y' => 40],
                'size' => ['width' => 720, 'height' => 60],
                'config' => [
                    'content' => '<h1>School Performance Dashboard</h1>',
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
                    'label' => 'Total Students',
                    'data_source' => 'student_count',
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
                    'label' => 'Good Standing',
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
                    'label' => 'At Risk',
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
                    'label' => 'Avg GPA',
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
                    'title' => 'School-wide Trends',
                    'metric_keys' => ['average_gpa', 'average_attendance', 'average_wellness'],
                    'aggregation' => 'school',
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
                    'title' => 'Risk Distribution',
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
                    'prompt' => 'Provide an executive summary of school performance with key insights and recommendations.',
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
