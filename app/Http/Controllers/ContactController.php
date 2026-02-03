<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Participant;
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
     * Display a participant contact view.
     */
    public function show(Request $request, Participant $participant)
    {
        // TODO: Add LearnerPolicy for proper authorization
        // $this->authorize('view', $participant);

        $participant->load([
            'user',
            'organization',
            'surveyAttempts',
            'resourceAssignments.resource',
            'strategicPlans',
        ]);

        $user = auth()->user();
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        // Log privacy-compliant access
        AuditLog::log('view', $participant, null, null, $participant);

        // Get heat map data for the participant
        $heatMapData = $this->metricService->getHeatMapData(
            $participant,
            $organizationYear,
            ['academics', 'attendance', 'behavior', 'life_skills']
        );

        // Get chart data for the last 12 months
        $chartData = $this->metricService->getChartData(
            Participant::class,
            $participant->id,
            ['gpa', 'wellness_score', 'emotional_wellbeing', 'engagement_score', 'plan_progress'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        // Get resource suggestions (pending and recently reviewed)
        $resourceSuggestions = $participant->resourceSuggestions()
            ->with('resource')
            ->whereIn('status', ['pending', 'accepted'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get suggested resources based on risk level (legacy, for backward compatibility)
        $suggestedResources = \App\Models\Resource::forOrganization($participant->org_id)
            ->active()
            ->whereJsonContains('target_risk_levels', $participant->risk_level)
            ->limit(5)
            ->get();

        return view('contacts.participant-view', compact(
            'participant',
            'suggestedResources',
            'heatMapData',
            'chartData',
            'resourceSuggestions',
            'organizationYear'
        ));
    }

    /**
     * Display an instructor contact view.
     */
    public function showInstructor(Request $request, User $instructor)
    {
        // Verify the user is a instructor
        if (! $instructor->hasRole('instructor')) {
            abort(404);
        }

        // TODO: Add UserPolicy for proper authorization
        // $this->authorize('view', $instructor);

        $instructor->load(['organization']);

        $user = auth()->user();
        $organizationYear = $request->get('organization_year', $this->metricService->getCurrentOrganizationYear());

        // Log access
        AuditLog::log('view', $instructor);

        // Get chart data for learning_group and PD metrics
        $chartData = $this->metricService->getChartData(
            User::class,
            $instructor->id,
            ['learning_group_performance', 'learner_growth', 'pd_progress'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        // Get learning_group metrics
        $learningGroupMetrics = $instructor->learningGroupMetrics()
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        // Get PD metrics
        $pdMetrics = $instructor->pdMetrics()
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return view('contacts.instructor-view', compact(
            'instructor',
            'chartData',
            'learningGroupMetrics',
            'pdMetrics',
            'organizationYear'
        ));
    }

    /**
     * Display a direct supervisor contact view.
     */
    public function showDirectSupervisor(Request $request, User $direct_supervisor)
    {
        // Verify the user is a direct_supervisor
        if (! $direct_supervisor->hasRole('direct_supervisor')) {
            abort(404);
        }

        // TODO: Add UserPolicy for proper authorization
        // $this->authorize('view', $direct_supervisor);

        $direct_supervisor->load(['organization']);

        $user = auth()->user();

        // Log access
        AuditLog::log('view', $direct_supervisor);

        // Get linked participants
        $linkedLearners = Participant::where('org_id', $direct_supervisor->org_id)
            ->whereHas('guardians', function ($query) use ($direct_supervisor) {
                $query->where('users.id', $direct_supervisor->id);
            })
            ->with(['metrics' => function ($query) {
                $query->latest('recorded_at')->limit(5);
            }])
            ->get();

        // Get engagement metrics for direct_supervisor
        $engagementMetrics = $direct_supervisor->metrics()
            ->where('metric_category', 'engagement')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        // Get chart data for direct_supervisor engagement
        $chartData = $this->metricService->getChartData(
            User::class,
            $direct_supervisor->id,
            ['engagement_score', 'communication_frequency'],
            Carbon::now()->subMonths(12),
            Carbon::now(),
            'month'
        );

        return view('contacts.direct_supervisor-view', compact(
            'direct_supervisor',
            'linkedLearners',
            'engagementMetrics',
            'chartData'
        ));
    }
}
