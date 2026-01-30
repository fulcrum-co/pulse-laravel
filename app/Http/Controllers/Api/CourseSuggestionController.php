<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MiniCourseSuggestion;
use App\Models\Student;
use App\Services\AdaptiveTriggerService;
use App\Services\MiniCourseGenerationService;
use App\Services\ProviderMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseSuggestionController extends Controller
{
    public function __construct(
        protected MiniCourseGenerationService $courseGenerationService,
        protected AdaptiveTriggerService $triggerService,
        protected ProviderMatchingService $providerMatchingService
    ) {}

    /**
     * Get suggestions for a contact (student).
     */
    public function index(Request $request, string $contactType, int $contactId): JsonResponse
    {
        $user = auth()->user();

        // Map contact type
        $fullContactType = match ($contactType) {
            'student' => Student::class,
            default => $contactType,
        };

        $query = MiniCourseSuggestion::where('contact_type', $fullContactType)
            ->where('contact_id', $contactId)
            ->where('org_id', $user->org_id)
            ->with(['miniCourse', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->where('status', MiniCourseSuggestion::STATUS_PENDING);
        }

        $suggestions = $query->orderByDesc('relevance_score')->paginate(10);

        return response()->json($suggestions);
    }

    /**
     * Generate new suggestions for a student.
     */
    public function generate(Request $request, Student $student): JsonResponse
    {
        // Verify org access
        if ($student->org_id !== auth()->user()->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $signals = $request->input('signals', []);

        // Generate course suggestions
        $courseSuggestion = $this->courseGenerationService->generateCourseSuggestion($student, $signals);

        // Also get provider recommendations
        $providerRecommendations = $this->providerMatchingService->findMatchingProviders($student, [], 3);

        // Get program recommendations
        $programRecommendations = $this->providerMatchingService->findMatchingPrograms($student, [], 3);

        return response()->json([
            'success' => true,
            'course_suggestion' => $courseSuggestion,
            'provider_recommendations' => $providerRecommendations,
            'program_recommendations' => $programRecommendations,
        ]);
    }

    /**
     * Evaluate triggers for a student.
     */
    public function evaluateTriggers(Student $student): JsonResponse
    {
        // Verify org access
        if ($student->org_id !== auth()->user()->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $results = $this->triggerService->evaluateTriggersForStudent($student);

        return response()->json([
            'success' => true,
            'triggers_evaluated' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Accept a suggestion.
     */
    public function accept(Request $request, MiniCourseSuggestion $suggestion): JsonResponse
    {
        $user = auth()->user();

        if ($suggestion->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $enrollment = $suggestion->accept($user->id, $request->input('notes'));

        AuditLog::log('update', $suggestion, ['status' => 'pending'], ['status' => 'accepted']);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion->fresh(),
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Decline a suggestion.
     */
    public function decline(Request $request, MiniCourseSuggestion $suggestion): JsonResponse
    {
        $user = auth()->user();

        if ($suggestion->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $suggestion->decline($user->id, $request->input('reason'));

        AuditLog::log('update', $suggestion, ['status' => 'pending'], ['status' => 'declined']);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion->fresh(),
        ]);
    }

    /**
     * Get AI provider recommendations for a student.
     */
    public function providerRecommendations(Request $request, Student $student): JsonResponse
    {
        // Verify org access
        if ($student->org_id !== auth()->user()->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $context = [
            'needs' => $request->input('needs', []),
            'preferences' => $request->input('preferences', []),
        ];

        $recommendations = $this->providerMatchingService->getAiProviderRecommendations($student, $context);

        return response()->json($recommendations);
    }

    /**
     * Get student signals (for debugging/transparency).
     */
    public function signals(Student $student): JsonResponse
    {
        // Verify org access
        if ($student->org_id !== auth()->user()->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $signals = $this->triggerService->gatherInputSignals($student);

        return response()->json([
            'student_id' => $student->id,
            'signals' => $signals,
        ]);
    }
}
