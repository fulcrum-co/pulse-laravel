<?php

namespace App\Http\Controllers;

use App\Models\ProgressSummary;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use App\Services\PlanProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressUpdateController extends Controller
{
    public function __construct(
        protected PlanProgressService $progressService
    ) {}

    /**
     * List progress updates for a plan.
     */
    public function index(Request $request, StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('view', $strategy);

        $query = $strategy->progressUpdates()->with('creator', 'goal', 'keyResult', 'milestone');

        // Filter by type
        if ($request->has('type')) {
            $query->where('update_type', $request->type);
        }

        // Filter by date range
        if ($request->has('days')) {
            $query->recent((int) $request->days);
        }

        $updates = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'updates' => $updates,
        ]);
    }

    /**
     * Store a new progress update.
     */
    public function store(Request $request, StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('update', $strategy);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'goal_id' => 'nullable|exists:goals,id',
            'key_result_id' => 'nullable|exists:key_results,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'value_change' => 'nullable|numeric',
            'status_change' => 'nullable|string|max:50',
            'attachments' => 'nullable|array',
        ]);

        $update = ProgressUpdate::create([
            'strategic_plan_id' => $strategy->id,
            ...$validated,
            'update_type' => ProgressUpdate::TYPE_MANUAL,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'update' => $update->load('creator', 'goal', 'keyResult', 'milestone'),
        ]);
    }

    /**
     * Generate an AI progress summary.
     */
    public function generateSummary(Request $request, StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('update', $strategy);

        $validated = $request->validate([
            'period_type' => 'sometimes|in:weekly,monthly,quarterly',
        ]);

        $periodType = $validated['period_type'] ?? ProgressSummary::PERIOD_WEEKLY;

        $summary = $this->progressService->generateProgressSummary($strategy, $periodType);

        if (! $summary) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate progress summary',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Get progress analytics for a plan.
     */
    public function analytics(StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('view', $strategy);

        return response()->json([
            'success' => true,
            'progress' => $this->progressService->calculatePlanProgress($strategy),
            'upcoming_milestones' => $this->progressService->getUpcomingMilestones($strategy),
            'overdue_items' => $this->progressService->getOverdueItems($strategy),
            'recent_summaries' => $strategy->progressSummaries()->latest()->take(3)->get(),
        ]);
    }
}
