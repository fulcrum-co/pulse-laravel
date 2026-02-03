<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Learner;
use App\Models\User;
use App\Services\ContactMetricService;
use App\Services\ResourceSuggestionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        protected ContactMetricService $metricService,
        protected ResourceSuggestionService $suggestionService
    ) {}

    /**
     * Display the contacts index page.
     */
    public function index(Request $request)
    {
        return view('contacts.index');
    }

    /**
     * Display a learner contact view.
     */
    public function show(Request $request, Learner $learner)
    {
        // TODO: Add LearnerPolicy for proper authorization
        // $this->authorize('view', $learner);

        $learner->load([
            'user',
            'organization',
            'surveyAttempts',
            'resourceAssignments.resource',
            'strategicPlans',
        ]);

        $user = auth()->user();
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        // Log FERPA-compliant access
        AuditLog::log('view', $learner, null, null, $learner);

        // Get heat map data for the learner
        $heatMapData = $this->metricService->getHeatMapData(
            $learner,
            $organizationYear,
            ['academics', 'attendance', 'behavior', 'life_skills']
        );

        // Get chart data for the last 12 months
        $chartData = $this->metricService->getChartData(
            Learner::class,
            $learner->id,
            ['gpa', 'wellness_score', 'emotional_wellbeing', 'engagement_score', 'plan_progress'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        // Get resource suggestions (pending and recently reviewed)
        $resourceSuggestions = $learner->resourceSuggestions()
            ->with('resource')
            ->whereIn('status', ['pending', 'accepted'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get suggested resources based on risk level (legacy, for backward compatibility)
        $suggestedResources = \App\Models\Resource::forOrganization($learner->org_id)
            ->active()
            ->whereJsonContains('target_risk_levels', $learner->risk_level)
            ->limit(5)
            ->get();

        return view('contacts.learner-view', compact(
            'learner',
            'suggestedResources',
            'heatMapData',
            'chartData',
            'resourceSuggestions',
            'organizationYear'
        ));
    }

    /**
     * Display a teacher contact view.
     */
    public function showTeacher(Request $request, User $teacher)
    {
        // Verify the user is a teacher
        if (! $teacher->hasRole('teacher')) {
            abort(404);
        }

        // TODO: Add UserPolicy for proper authorization
        // $this->authorize('view', $teacher);

        $teacher->load(['organization']);

        $user = auth()->user();
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        // Log access
        AuditLog::log('view', $teacher);

        // Get chart data for classroom and PD metrics
        $chartData = $this->metricService->getChartData(
            User::class,
            $teacher->id,
            ['classroom_performance', 'learner_growth', 'pd_progress'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        // Get classroom metrics
        $classroomMetrics = $teacher->classroomMetrics()
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        // Get PD metrics
        $pdMetrics = $teacher->pdMetrics()
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return view('contacts.teacher-view', compact(
            'teacher',
            'chartData',
            'classroomMetrics',
            'pdMetrics',
            'organizationYear'
        ));
    }

    /**
     * Display a parent contact view.
     */
    public function showParent(Request $request, User $parent)
    {
        // Verify the user is a parent
        if (! $parent->hasRole('parent')) {
            abort(404);
        }

        // TODO: Add UserPolicy for proper authorization
        // $this->authorize('view', $parent);

        $parent->load(['organization']);

        $user = auth()->user();

        // Log access
        AuditLog::log('view', $parent);

        // Get linked learners (children)
        $linkedLearners = Learner::where('org_id', $parent->org_id)
            ->whereHas('guardians', function ($query) use ($parent) {
                $query->where('users.id', $parent->id);
            })
            ->with(['metrics' => function ($query) {
                $query->latest('recorded_at')->limit(5);
            }])
            ->get();

        // Get engagement metrics for parent
        $engagementMetrics = $parent->metrics()
            ->where('metric_category', 'engagement')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        // Get chart data for parent engagement
        $chartData = $this->metricService->getChartData(
            User::class,
            $parent->id,
            ['engagement_score', 'communication_frequency'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        return view('contacts.parent-view', compact(
            'parent',
            'linkedLearners',
            'engagementMetrics',
            'chartData'
        ));
    }
}
