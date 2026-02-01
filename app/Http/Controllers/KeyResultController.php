<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\KeyResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KeyResultController extends Controller
{
    /**
     * Store a new key result for a goal.
     */
    public function store(Request $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal->strategicPlan);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'metric_type' => 'required|in:percentage,number,currency,boolean,milestone',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'starting_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
        ]);

        $maxSortOrder = $goal->keyResults()->max('sort_order') ?? 0;

        $keyResult = $goal->keyResults()->create([
            ...$validated,
            'current_value' => $validated['current_value'] ?? $validated['starting_value'] ?? 0,
            'starting_value' => $validated['starting_value'] ?? 0,
            'status' => KeyResult::STATUS_NOT_STARTED,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'key_result' => $keyResult,
        ]);
    }

    /**
     * Update an existing key result.
     */
    public function update(Request $request, KeyResult $keyResult): JsonResponse
    {
        $this->authorize('update', $keyResult->goal->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'metric_type' => 'sometimes|in:percentage,number,currency,boolean,milestone',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'starting_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:not_started,in_progress,on_track,at_risk,completed',
        ]);

        // If current_value is being updated, use the updateValue method
        if (isset($validated['current_value']) && $validated['current_value'] != $keyResult->current_value) {
            $keyResult->updateValue($validated['current_value'], auth()->id());
            unset($validated['current_value']);
        }

        $keyResult->update($validated);

        return response()->json([
            'success' => true,
            'key_result' => $keyResult->fresh(),
        ]);
    }

    /**
     * Delete a key result.
     */
    public function destroy(KeyResult $keyResult): JsonResponse
    {
        $this->authorize('update', $keyResult->goal->strategicPlan);

        $goal = $keyResult->goal;
        $keyResult->delete();

        // Update parent goal status
        $goal->updateStatusFromKeyResults();

        return response()->json([
            'success' => true,
        ]);
    }
}
