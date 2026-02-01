<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    /**
     * Store a new milestone for a plan.
     */
    public function store(Request $request, StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('update', $strategy);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'goal_id' => 'nullable|exists:goals,id',
        ]);

        $maxSortOrder = $strategy->milestones()->max('sort_order') ?? 0;

        $milestone = $strategy->milestones()->create([
            ...$validated,
            'status' => Milestone::STATUS_PENDING,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'milestone' => $milestone->load('goal'),
        ]);
    }

    /**
     * Update an existing milestone.
     */
    public function update(Request $request, Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|in:pending,in_progress,completed,missed',
            'goal_id' => 'nullable|exists:goals,id',
        ]);

        $milestone->update($validated);

        return response()->json([
            'success' => true,
            'milestone' => $milestone->fresh()->load('goal'),
        ]);
    }

    /**
     * Mark a milestone as complete.
     */
    public function complete(Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone->strategicPlan);

        $milestone->markComplete(auth()->id());

        // Create a system progress update
        ProgressUpdate::create([
            'strategic_plan_id' => $milestone->strategic_plan_id,
            'milestone_id' => $milestone->id,
            'goal_id' => $milestone->goal_id,
            'content' => "Milestone completed: {$milestone->title}",
            'update_type' => ProgressUpdate::TYPE_SYSTEM,
            'status_change' => 'completed',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'milestone' => $milestone->fresh()->load('completedByUser'),
        ]);
    }

    /**
     * Delete a milestone.
     */
    public function destroy(Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone->strategicPlan);

        $milestone->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
