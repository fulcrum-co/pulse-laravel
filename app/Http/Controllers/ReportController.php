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
            // === DOCUMENT TEMPLATES ===
            [
                'id' => 'student_progress',
                'name' => 'Student Progress Report',
                'description' => 'Individual student performance tracking with metrics over time',
                'thumbnail' => '/images/templates/student-progress.png',
                'type' => CustomReport::TYPE_STUDENT_PROGRESS,
                'category' => 'document',
                'layout' => $this->getStudentProgressLayout(),
            ],
            [
                'id' => 'cohort_summary',
                'name' => 'Cohort Summary',
                'description' => 'Aggregate metrics for a group of students',
                'thumbnail' => '/images/templates/cohort-summary.png',
                'type' => CustomReport::TYPE_COHORT_SUMMARY,
                'category' => 'document',
                'layout' => $this->getCohortSummaryLayout(),
            ],
            [
                'id' => 'school_dashboard',
                'name' => 'School Dashboard',
                'description' => 'School-wide analytics and KPIs',
                'thumbnail' => '/images/templates/school-dashboard.png',
                'type' => CustomReport::TYPE_SCHOOL_DASHBOARD,
                'category' => 'document',
                'layout' => $this->getSchoolDashboardLayout(),
            ],
            [
                'id' => 'blank_document',
                'name' => 'Blank Document',
                'description' => 'Start from scratch with a blank document',
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'document',
                'layout' => [],
            ],

            // === WIDGET TEMPLATES ===
            [
                'id' => 'kpi_widget',
                'name' => 'KPI Dashboard Widget',
                'description' => 'Single metric display for embedding on websites',
                'thumbnail' => '/images/templates/widget-kpi.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'widget',
                'layout' => $this->getKpiWidgetLayout(),
            ],
            [
                'id' => 'chart_widget',
                'name' => 'Chart Widget',
                'description' => 'Embeddable chart with trend visualization',
                'thumbnail' => '/images/templates/widget-chart.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'widget',
                'layout' => $this->getChartWidgetLayout(),
            ],
            [
                'id' => 'stats_banner',
                'name' => 'Stats Banner',
                'description' => 'Horizontal banner with multiple KPIs',
                'thumbnail' => '/images/templates/widget-banner.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'widget',
                'layout' => $this->getStatsBannerLayout(),
            ],
            [
                'id' => 'blank_widget',
                'name' => 'Blank Widget',
                'description' => 'Start from scratch with a blank widget',
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'widget',
                'layout' => [],
            ],

            // === SOCIAL TEMPLATES ===
            [
                'id' => 'achievement_post',
                'name' => 'Achievement Celebration',
                'description' => 'Share student achievements on social media',
                'thumbnail' => '/images/templates/social-achievement.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'social',
                'layout' => $this->getAchievementPostLayout(),
            ],
            [
                'id' => 'stats_infographic',
                'name' => 'Stats Infographic',
                'description' => 'Eye-catching statistics for social sharing',
                'thumbnail' => '/images/templates/social-stats.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'social',
                'layout' => $this->getStatsInfographicLayout(),
            ],
            [
                'id' => 'announcement_post',
                'name' => 'Announcement Post',
                'description' => 'School news and announcements',
                'thumbnail' => '/images/templates/social-announcement.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'social',
                'layout' => $this->getAnnouncementPostLayout(),
            ],
            [
                'id' => 'blank_social',
                'name' => 'Blank Social Post',
                'description' => 'Start from scratch',
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'social',
                'layout' => [],
            ],

            // === CUSTOM TEMPLATES ===
            [
                'id' => 'blank_custom',
                'name' => 'Blank Canvas',
                'description' => 'Custom dimensions, start fresh',
                'thumbnail' => '/images/templates/blank.png',
                'type' => CustomReport::TYPE_CUSTOM,
                'category' => 'custom',
                'layout' => [],
            ],
        ];
    }

    /**
     * KPI Widget Layout (300x250 medium rectangle)
     */
    protected function getKpiWidgetLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 20, 'y' => 20],
                'size' => ['width' => 260, 'height' => 120],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => 'Average GPA',
                    'show_trend' => true,
                    'comparison_period' => 'last_month',
                ],
                'styles' => [
                    'backgroundColor' => '#F0F9FF',
                    'borderRadius' => 12,
                    'padding' => 20,
                    'valueColor' => '#1E40AF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 20, 'y' => 160],
                'size' => ['width' => 260, 'height' => 70],
                'config' => [
                    'content' => '<p style="text-align: center; color: #6B7280; font-size: 12px;">Updated in real-time</p>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'textAlign' => 'center',
                ],
            ],
        ];
    }

    /**
     * Chart Widget Layout (728x90 leaderboard)
     */
    protected function getChartWidgetLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'chart',
                'position' => ['x' => 10, 'y' => 10],
                'size' => ['width' => 280, 'height' => 230],
                'config' => [
                    'chart_type' => 'line',
                    'title' => 'Performance Trend',
                    'metric_keys' => ['gpa'],
                    'colors' => ['#3B82F6'],
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 12,
                ],
            ],
        ];
    }

    /**
     * Stats Banner Layout (970x250 billboard)
     */
    protected function getStatsBannerLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'heading',
                'position' => ['x' => 20, 'y' => 20],
                'size' => ['width' => 930, 'height' => 40],
                'config' => [
                    'content' => '<h2>School Performance Dashboard</h2>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 24,
                    'fontWeight' => 'bold',
                    'textAlign' => 'center',
                    'color' => '#111827',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 20, 'y' => 80],
                'size' => ['width' => 220, 'height' => 150],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => 'Average GPA',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#EFF6FF', 'borderRadius' => 12, 'padding' => 16],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 260, 'y' => 80],
                'size' => ['width' => 220, 'height' => 150],
                'config' => [
                    'metric_key' => 'attendance_rate',
                    'label' => 'Attendance Rate',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#F0FDF4', 'borderRadius' => 12, 'padding' => 16],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 500, 'y' => 80],
                'size' => ['width' => 220, 'height' => 150],
                'config' => [
                    'metric_key' => 'wellness_score',
                    'label' => 'Wellness Score',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FDF4FF', 'borderRadius' => 12, 'padding' => 16],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 740, 'y' => 80],
                'size' => ['width' => 210, 'height' => 150],
                'config' => [
                    'metric_key' => 'engagement_score',
                    'label' => 'Engagement',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FFF7ED', 'borderRadius' => 12, 'padding' => 16],
            ],
        ];
    }

    /**
     * Achievement Post Layout (1080x1080 Instagram)
     */
    protected function getAchievementPostLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'rectangle',
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 1080, 'height' => 1080],
                'config' => [],
                'styles' => [
                    'backgroundColor' => '#1E40AF',
                    'borderRadius' => 0,
                    'opacity' => 100,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'heading',
                'position' => ['x' => 80, 'y' => 120],
                'size' => ['width' => 920, 'height' => 100],
                'config' => [
                    'content' => '<h1>🎉 Congratulations!</h1>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 56,
                    'fontWeight' => 'bold',
                    'textAlign' => 'center',
                    'color' => '#FFFFFF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 240, 'y' => 320],
                'size' => ['width' => 600, 'height' => 300],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => 'Outstanding Achievement',
                    'show_trend' => false,
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 24,
                    'padding' => 40,
                    'valueColor' => '#1E40AF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 80, 'y' => 700],
                'size' => ['width' => 920, 'height' => 120],
                'config' => [
                    'content' => '<p style="text-align: center;">Student Name has achieved exceptional results this semester!</p>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 28,
                    'textAlign' => 'center',
                    'color' => '#FFFFFF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'logo',
                'position' => ['x' => 440, 'y' => 900],
                'size' => ['width' => 200, 'height' => 80],
                'config' => ['use_org_logo' => true, 'fit' => 'contain'],
                'styles' => ['borderRadius' => 0],
            ],
        ];
    }

    /**
     * Stats Infographic Layout (1080x1080 Instagram)
     */
    protected function getStatsInfographicLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'rectangle',
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 1080, 'height' => 1080],
                'config' => [],
                'styles' => [
                    'backgroundColor' => '#F97316',
                    'borderRadius' => 0,
                    'opacity' => 100,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'heading',
                'position' => ['x' => 80, 'y' => 80],
                'size' => ['width' => 920, 'height' => 100],
                'config' => [
                    'content' => '<h1>📊 By The Numbers</h1>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 48,
                    'fontWeight' => 'bold',
                    'textAlign' => 'center',
                    'color' => '#FFFFFF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 80, 'y' => 240],
                'size' => ['width' => 440, 'height' => 200],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => 'Average GPA',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 20, 'padding' => 24],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 560, 'y' => 240],
                'size' => ['width' => 440, 'height' => 200],
                'config' => [
                    'metric_key' => 'attendance_rate',
                    'label' => 'Attendance',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 20, 'padding' => 24],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 80, 'y' => 480],
                'size' => ['width' => 440, 'height' => 200],
                'config' => [
                    'metric_key' => 'wellness_score',
                    'label' => 'Wellness',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 20, 'padding' => 24],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => 560, 'y' => 480],
                'size' => ['width' => 440, 'height' => 200],
                'config' => [
                    'metric_key' => 'engagement_score',
                    'label' => 'Engagement',
                    'show_trend' => true,
                ],
                'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 20, 'padding' => 24],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 80, 'y' => 740],
                'size' => ['width' => 920, 'height' => 80],
                'config' => [
                    'content' => '<p style="text-align: center;">This Semester\'s Highlights</p>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 24,
                    'textAlign' => 'center',
                    'color' => '#FFFFFF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'logo',
                'position' => ['x' => 440, 'y' => 900],
                'size' => ['width' => 200, 'height' => 80],
                'config' => ['use_org_logo' => true, 'fit' => 'contain'],
                'styles' => ['borderRadius' => 0],
            ],
        ];
    }

    /**
     * Announcement Post Layout (1080x1080 Instagram)
     */
    protected function getAnnouncementPostLayout(): array
    {
        return [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'rectangle',
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 1080, 'height' => 1080],
                'config' => [],
                'styles' => [
                    'backgroundColor' => '#10B981',
                    'borderRadius' => 0,
                    'opacity' => 100,
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'heading',
                'position' => ['x' => 80, 'y' => 200],
                'size' => ['width' => 920, 'height' => 120],
                'config' => [
                    'content' => '<h1>📢 Announcement</h1>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => 'transparent',
                    'fontSize' => 56,
                    'fontWeight' => 'bold',
                    'textAlign' => 'center',
                    'color' => '#FFFFFF',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'position' => ['x' => 120, 'y' => 400],
                'size' => ['width' => 840, 'height' => 400],
                'config' => [
                    'content' => '<p style="text-align: center; font-size: 32px; line-height: 1.6;">Your announcement message goes here. Share important updates, news, and information with your community.</p>',
                    'format' => 'html',
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 24,
                    'padding' => 40,
                    'fontSize' => 24,
                    'textAlign' => 'center',
                    'color' => '#111827',
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'logo',
                'position' => ['x' => 440, 'y' => 900],
                'size' => ['width' => 200, 'height' => 80],
                'config' => ['use_org_logo' => true, 'fit' => 'contain'],
                'styles' => ['borderRadius' => 0],
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
