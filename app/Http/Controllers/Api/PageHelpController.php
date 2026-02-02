<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageHelpHint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageHelpController extends Controller
{
    /**
     * Get all help hints grouped by page context.
     */
    public function allHints(Request $request): JsonResponse
    {
        $orgId = $request->user()?->org_id;

        $hints = PageHelpHint::getAllHintsGrouped($orgId);

        return response()->json([
            'hints' => $hints,
        ]);
    }

    /**
     * Get help hints for a specific page.
     */
    public function pageHints(Request $request, string $context): JsonResponse
    {
        $orgId = $request->user()?->org_id;

        // Validate context
        if (! array_key_exists($context, PageHelpHint::CONTEXTS)) {
            return response()->json(['error' => 'Invalid page context'], 400);
        }

        $hints = PageHelpHint::getHintsForPage($context, $orgId);

        return response()->json([
            'hints' => $hints,
        ]);
    }

    /**
     * Store a new help hint (for visual editor).
     */
    public function store(Request $request): JsonResponse
    {
        // Verify user is admin
        if (! $request->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'page_context' => 'required|string|in:'.implode(',', array_keys(PageHelpHint::CONTEXTS)),
            'section' => 'required|string|max:50|regex:/^[a-z0-9\-]+$/',
            'selector' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'video_url' => 'nullable|url|max:500',
            'position' => 'required|in:top,bottom,left,right',
            'offset_x' => 'nullable|integer|min:0|max:5000',
            'offset_y' => 'nullable|integer|min:0|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['org_id'] = null; // System-wide hints
        $data['is_active'] = true;
        $data['sort_order'] = PageHelpHint::where('page_context', $data['page_context'])
            ->whereNull('org_id')
            ->max('sort_order') + 1;

        $hint = PageHelpHint::create($data);

        return response()->json([
            'success' => true,
            'hint' => $hint,
        ], 201);
    }

    /**
     * Update an existing help hint.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Verify user is admin
        if (! $request->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hint = PageHelpHint::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'section' => 'sometimes|string|max:50|regex:/^[a-z0-9\-]+$/',
            'selector' => 'sometimes|nullable|string|max:255',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'video_url' => 'nullable|url|max:500',
            'position' => 'sometimes|in:top,bottom,left,right',
            'offset_x' => 'nullable|integer|min:0|max:5000',
            'offset_y' => 'nullable|integer|min:0|max:5000',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hint->update($validator->validated());

        return response()->json([
            'success' => true,
            'hint' => $hint->fresh(),
        ]);
    }

    /**
     * Delete a help hint.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Verify user is admin
        if (! $request->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hint = PageHelpHint::findOrFail($id);
        $hint->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Batch update hint positions (for drag-and-drop repositioning).
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        // Verify user is admin
        if (! $request->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'hints' => 'required|array',
            'hints.*.id' => 'required|integer|exists:page_help_hints,id',
            'hints.*.offset_x' => 'nullable|integer|min:0|max:5000',
            'hints.*.offset_y' => 'nullable|integer|min:0|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->hints as $hintData) {
            PageHelpHint::where('id', $hintData['id'])->update([
                'offset_x' => $hintData['offset_x'] ?? 0,
                'offset_y' => $hintData['offset_y'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
