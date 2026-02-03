<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContactResourceSuggestion;
use App\Services\ResourceSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceSuggestionController extends Controller
{
    public function __construct(
        protected ResourceSuggestionService $suggestionService
    ) {}

    /**
     * Get suggestions for a contact.
     */
    public function index(Request $request, string $contactType, int $contactId): JsonResponse
    {
        // Map shorthand contact type to full class name
        $typeMap = [
            'participant' => 'App\\Models\\Participant',
            'user' => 'App\\Models\\User',
        ];
        $fullType = $typeMap[$contactType] ?? $contactType;

        $suggestions = ContactResourceSuggestion::forContact($fullType, $contactId)
            ->with('resource')
            ->orderByDesc('relevance_score')
            ->get();

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Create a manual suggestion.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:participant,user,App\\Models\\Participant,App\\Models\\User',
            'contact_id' => 'required|integer',
            'resource_id' => 'required|exists:resources,id',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'participant' => 'App\\Models\\Participant',
            'user' => 'App\\Models\\User',
        ];
        $contactType = $typeMap[$validated['contact_type']] ?? $validated['contact_type'];

        $suggestion = $this->suggestionService->manualSuggest(
            $contactType,
            $validated['contact_id'],
            $validated['resource_id'],
            $user->id,
            $validated['notes'] ?? null
        );

        AuditLog::log('create', $suggestion);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion->load('resource'),
        ], 201);
    }

    /**
     * Review (accept/decline) a suggestion.
     */
    public function review(Request $request, ContactResourceSuggestion $suggestion): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,decline',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        if ($validated['action'] === 'accept') {
            $assignment = $this->suggestionService->acceptSuggestion($suggestion, $user->id);
            AuditLog::log('update', $suggestion);

            return response()->json([
                'success' => true,
                'action' => 'accepted',
                'assignment' => $assignment,
            ]);
        }

        $this->suggestionService->declineSuggestion($suggestion, $user->id, $validated['notes'] ?? null);
        AuditLog::log('update', $suggestion);

        return response()->json([
            'success' => true,
            'action' => 'declined',
        ]);
    }

    /**
     * Generate AI suggestions for a contact.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:participant',
            'contact_id' => 'required|integer|exists:participants,id',
        ]);

        $participant = \App\Models\Participant::findOrFail($validated['contact_id']);

        // Check if AI suggestions are enabled
        if (config('pulse.contact_view.ai_suggestions.enabled', false)) {
            $suggestions = $this->suggestionService->generateAiSuggestions($participant);
        } else {
            $suggestions = $this->suggestionService->generateRuleBasedSuggestions($participant);
        }

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions->load('resource'),
        ]);
    }
}
