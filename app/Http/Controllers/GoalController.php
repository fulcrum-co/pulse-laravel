<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\StrategicPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    /**
     * Store a new goal for a plan.
     */
    public function store(Request $request, StrategicPlan $strategy): JsonResponse
    {
        $this->authorize('update', $strategy);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_type' => 'nullable|in:objective,key_result,outcome',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'parent_goal_id' => 'nullable|exists:goals,id',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $maxSortOrder = $strategy->goals()->max('sort_order') ?? 0;

        $goal = $strategy->allGoals()->create([
            ...$validated,
            'goal_type' => $validated['goal_type'] ?? 'objective',
            'status' => Goal::STATUS_NOT_STARTED,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'goal' => $goal->load('owner', 'keyResults'),
        ]);
    }

    /**
     * Update an existing goal.
     */
    public function update(Request $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'goal_type' => 'sometimes|in:objective,key_result,outcome',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:not_started,in_progress,at_risk,completed',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $goal->update($validated);

        // Update status from key results if applicable
        if ($goal->keyResults->isNotEmpty()) {
            $goal->updateStatusFromKeyResults();
        }

        return response()->json([
            'success' => true,
            'goal' => $goal->fresh()->load('owner', 'keyResults'),
        ]);
    }

    /**
     * Delete a goal.
     */
    public function destroy(Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal->strategicPlan);

        $goal->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reorder goals.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'goals' => 'required|array',
            'goals.*.id' => 'required|exists:goals,id',
            'goals.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['goals'] as $goalData) {
            $goal = Goal::find($goalData['id']);
            if ($goal) {
                $this->authorize('update', $goal->strategicPlan);
                $goal->update(['sort_order' => $goalData['sort_order']]);
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
