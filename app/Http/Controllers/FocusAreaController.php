<?php

namespace App\Http\Controllers;

use App\Models\FocusArea;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;

class FocusAreaController extends Controller
{
    /**
     * Store a new focus area.
     */
    public function store(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeEdit($request->user(), $strategy);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Get next sort order
        $maxSortOrder = $strategy->focusAreas()->max('sort_order') ?? -1;

        $focusArea = FocusArea::create([
            'strategic_plan_id' => $strategy->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $maxSortOrder + 1,
            'status' => FocusArea::STATUS_NOT_STARTED,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'focusArea' => $focusArea,
            ]);
        }

        return back()->with('success', 'Focus area added successfully.');
    }

    /**
     * Update a focus area.
     */
    public function update(Request $request, FocusArea $focusArea)
    {
        $this->authorizeEdit($request->user(), $focusArea->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:on_track,at_risk,off_track,not_started',
        ]);

        $focusArea->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'focusArea' => $focusArea,
            ]);
        }

        return back()->with('success', 'Focus area updated successfully.');
    }

    /**
     * Delete a focus area.
     */
    public function destroy(Request $request, FocusArea $focusArea)
    {
        $this->authorizeEdit($request->user(), $focusArea->strategicPlan);

        $focusArea->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Focus area deleted successfully.');
    }

    /**
     * Reorder focus areas.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:focus_areas,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $focusArea = FocusArea::find($item['id']);
            $this->authorizeEdit($request->user(), $focusArea->strategicPlan);
            $focusArea->update(['sort_order' => $item['sort_order']]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Focus areas reordered successfully.');
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

        if (!$collaborator || !$collaborator->canEdit()) {
            if (!$user->isAdmin()) {
                abort(403, 'You do not have permission to edit this strategy.');
            }
        }
    }
}
