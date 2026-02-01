<?php

namespace App\Http\Controllers;

use App\Models\FocusArea;
use App\Models\Objective;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;

class ObjectiveController extends Controller
{
    /**
     * Store a new objective.
     */
    public function store(Request $request, FocusArea $focusArea)
    {
        $this->authorizeEdit($request->user(), $focusArea->strategicPlan);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Get next sort order
        $maxSortOrder = $focusArea->objectives()->max('sort_order') ?? -1;

        $objective = Objective::create([
            'focus_area_id' => $focusArea->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'sort_order' => $maxSortOrder + 1,
            'status' => Objective::STATUS_NOT_STARTED,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'objective' => $objective,
            ]);
        }

        return back()->with('success', 'Objective added successfully.');
    }

    /**
     * Update an objective.
     */
    public function update(Request $request, Objective $objective)
    {
        $this->authorizeEdit($request->user(), $objective->focusArea->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:on_track,at_risk,off_track,not_started',
        ]);

        $objective->update($validated);

        // If status changed, update parent focus area
        if (isset($validated['status'])) {
            $objective->focusArea->updateStatusFromChildren();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'objective' => $objective,
            ]);
        }

        return back()->with('success', 'Objective updated successfully.');
    }

    /**
     * Delete an objective.
     */
    public function destroy(Request $request, Objective $objective)
    {
        $this->authorizeEdit($request->user(), $objective->focusArea->strategicPlan);

        $focusArea = $objective->focusArea;
        $objective->delete();

        // Update parent focus area status
        $focusArea->updateStatusFromChildren();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Objective deleted successfully.');
    }

    /**
     * Reorder objectives.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:objectives,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $objective = Objective::find($item['id']);
            $this->authorizeEdit($request->user(), $objective->focusArea->strategicPlan);
            $objective->update(['sort_order' => $item['sort_order']]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Objectives reordered successfully.');
    }

    /**
     * Check if user can edit the strategy.
     */
    protected function authorizeEdit($user, StrategicPlan $strategy): void
    {
        if ($strategy->org_id !== $user->org_id) {
            abort(403, 'You do not have access to this strategy.');
        }

        $collaborator = $strategy->collaborators()->where('user_id', $user->id)->first();

        if (! $collaborator || ! $collaborator->canEdit()) {
            if (! $user->isAdmin()) {
                abort(403, 'You do not have permission to edit this strategy.');
            }
        }
    }
}
