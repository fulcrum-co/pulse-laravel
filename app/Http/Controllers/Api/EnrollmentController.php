<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseStepProgress;
use App\Models\Learner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * List enrollments for a learner.
     */
    public function indexByLearner(Request $request, Learner $learner): JsonResponse
    {
        $user = auth()->user();

        if ($learner->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $learner->miniCourseEnrollments()
            ->with(['miniCourse', 'currentStep']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderByDesc('created_at')->paginate(10);

        return response()->json($enrollments);
    }

    /**
     * List enrollments for a course.
     */
    public function indexByCourse(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('view', $course);

        $query = $course->enrollments()
            ->with(['learner.user', 'currentStep']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($enrollments);
    }

    /**
     * Enroll a learner in a course.
     */
    public function enroll(Request $request, MiniCourse $course, Learner $learner): JsonResponse
    {
        $user = auth()->user();

        // Verify access
        if ($course->org_id !== $user->org_id || $learner->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if course is active
        if ($course->status !== MiniCourse::STATUS_ACTIVE) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot enroll in a non-active course.',
            ], 422);
        }

        // Check for existing active enrollment
        $existing = MiniCourseEnrollment::where('mini_course_id', $course->id)
            ->where('learner_id', $learner->id)
            ->whereIn('status', [
                MiniCourseEnrollment::STATUS_ENROLLED,
                MiniCourseEnrollment::STATUS_IN_PROGRESS,
            ])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error' => 'Learner is already enrolled in this course.',
                'enrollment' => $existing,
            ], 422);
        }

        $enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $course->id,
            'mini_course_version_id' => $course->current_version_id,
            'learner_id' => $learner->id,
            'enrolled_by' => $user->id,
            'enrollment_source' => $request->input('source', MiniCourseEnrollment::SOURCE_MANUAL),
            'status' => MiniCourseEnrollment::STATUS_ENROLLED,
        ]);

        AuditLog::log('create', $enrollment);

        return response()->json([
            'success' => true,
            'enrollment' => $enrollment->load(['miniCourse', 'learner.user']),
        ], 201);
    }

    /**
     * Get enrollment details.
     */
    public function show(MiniCourseEnrollment $enrollment): JsonResponse
    {
        $user = auth()->user();

        // Load relationships and check access
        $enrollment->load(['miniCourse', 'learner', 'currentStep', 'stepProgress']);

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($enrollment);
    }

    /**
     * Update enrollment progress.
     */
    public function updateProgress(Request $request, MiniCourseEnrollment $enrollment): JsonResponse
    {
        $user = auth()->user();

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:enrolled,in_progress,completed,paused,withdrawn',
            'current_step_id' => 'sometimes|exists:mini_course_steps,id',
            'feedback' => 'nullable|array',
        ]);

        $oldValues = $enrollment->only(['status', 'current_step_id']);

        // Handle status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === MiniCourseEnrollment::STATUS_IN_PROGRESS && ! $enrollment->started_at) {
                $enrollment->started_at = now();
            }
            if ($validated['status'] === MiniCourseEnrollment::STATUS_COMPLETED && ! $enrollment->completed_at) {
                $enrollment->completed_at = now();
                $enrollment->progress_percent = 100;
            }
        }

        $enrollment->fill($validated);
        $enrollment->save();

        // Recalculate progress if not completed
        if ($enrollment->status !== MiniCourseEnrollment::STATUS_COMPLETED) {
            $enrollment->recalculateProgress();
        }

        AuditLog::log('update', $enrollment, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'enrollment' => $enrollment->fresh()->load(['miniCourse', 'currentStep']),
        ]);
    }

    /**
     * Complete a step.
     */
    public function completeStep(Request $request, MiniCourseEnrollment $enrollment, MiniCourseStep $step): JsonResponse
    {
        $user = auth()->user();

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($step->mini_course_id !== $enrollment->mini_course_id) {
            return response()->json(['error' => 'Step not part of this course'], 404);
        }

        // Get or create step progress
        $progress = MiniCourseStepProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
            ],
            [
                'status' => MiniCourseStepProgress::STATUS_NOT_STARTED,
            ]
        );

        // Update to completed
        $progress->update([
            'status' => MiniCourseStepProgress::STATUS_COMPLETED,
            'completed_at' => now(),
            'response_data' => $request->input('response_data'),
            'feedback_response' => $request->input('feedback_response'),
            'time_spent_seconds' => $request->input('time_spent_seconds', 0),
        ]);

        // Start enrollment if not started
        if ($enrollment->status === MiniCourseEnrollment::STATUS_ENROLLED) {
            $enrollment->start();
        }

        // Move to next step
        $nextStep = $step->next_step;
        if ($nextStep) {
            $enrollment->update(['current_step_id' => $nextStep->id]);
        }

        // Recalculate progress
        $enrollment->recalculateProgress();

        // Check if course is complete
        $totalSteps = $enrollment->miniCourse->steps()->where('is_required', true)->count();
        $completedSteps = $enrollment->stepProgress()->where('status', 'completed')->count();

        if ($completedSteps >= $totalSteps) {
            $enrollment->markCompleted();
        }

        AuditLog::log('update', $progress);

        return response()->json([
            'success' => true,
            'step_progress' => $progress,
            'enrollment' => $enrollment->fresh()->load(['currentStep', 'stepProgress']),
            'next_step' => $nextStep,
            'is_course_complete' => $enrollment->status === MiniCourseEnrollment::STATUS_COMPLETED,
        ]);
    }

    /**
     * Skip a step.
     */
    public function skipStep(Request $request, MiniCourseEnrollment $enrollment, MiniCourseStep $step): JsonResponse
    {
        $user = auth()->user();

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($step->mini_course_id !== $enrollment->mini_course_id) {
            return response()->json(['error' => 'Step not part of this course'], 404);
        }

        // Cannot skip required steps
        if ($step->is_required) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot skip a required step.',
            ], 422);
        }

        $progress = MiniCourseStepProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
            ],
            [
                'status' => MiniCourseStepProgress::STATUS_NOT_STARTED,
            ]
        );

        $progress->update([
            'status' => MiniCourseStepProgress::STATUS_SKIPPED,
            'completed_at' => now(),
        ]);

        // Move to next step
        $nextStep = $step->next_step;
        if ($nextStep) {
            $enrollment->update(['current_step_id' => $nextStep->id]);
        }

        $enrollment->recalculateProgress();

        return response()->json([
            'success' => true,
            'step_progress' => $progress,
            'enrollment' => $enrollment->fresh(),
            'next_step' => $nextStep,
        ]);
    }

    /**
     * Withdraw from a course.
     */
    public function withdraw(Request $request, MiniCourseEnrollment $enrollment): JsonResponse
    {
        $user = auth()->user();

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $oldStatus = $enrollment->status;

        $enrollment->update([
            'status' => MiniCourseEnrollment::STATUS_WITHDRAWN,
            'feedback' => array_merge($enrollment->feedback ?? [], [
                'withdrawal_reason' => $request->input('reason'),
                'withdrawn_at' => now()->toISOString(),
            ]),
        ]);

        AuditLog::log('update', $enrollment, ['status' => $oldStatus], ['status' => 'withdrawn']);

        return response()->json([
            'success' => true,
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Get step progress for an enrollment.
     */
    public function stepProgress(MiniCourseEnrollment $enrollment): JsonResponse
    {
        $user = auth()->user();

        if ($enrollment->miniCourse->org_id !== $user->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $steps = $enrollment->miniCourse->steps()
            ->orderBy('sort_order')
            ->get();

        $progress = $enrollment->stepProgress()->get()->keyBy('step_id');

        $stepsWithProgress = $steps->map(function ($step) use ($progress) {
            $stepProgress = $progress->get($step->id);

            return [
                'step' => $step,
                'progress' => $stepProgress,
                'status' => $stepProgress?->status ?? 'not_started',
                'completed_at' => $stepProgress?->completed_at,
                'time_spent_seconds' => $stepProgress?->time_spent_seconds ?? 0,
            ];
        });

        return response()->json([
            'enrollment_id' => $enrollment->id,
            'steps' => $stepsWithProgress,
            'total_steps' => $steps->count(),
            'completed_steps' => $progress->where('status', 'completed')->count(),
            'progress_percent' => $enrollment->progress_percent,
        ]);
    }
}
