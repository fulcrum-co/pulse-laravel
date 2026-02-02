<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageHelpHint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
