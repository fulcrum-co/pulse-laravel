<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Services\MiniCourseGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MiniCourseController extends Controller
{
    public function __construct(
        protected MiniCourseGenerationService $generationService
    ) {}

    /**
     * List all courses for the organization.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = MiniCourse::where('org_id', $user->org_id)
            ->withCount(['steps', 'enrollments'])
            ->with('creator');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by course type
        if ($request->has('course_type')) {
            $query->where('course_type', $request->course_type);
        }

        // Filter by template
        if ($request->boolean('templates_only')) {
            $query->where('is_template', true);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderByDesc('updated_at')->paginate(20);

        return response()->json($courses);
    }

    /**
     * Get a single course with all details.
     */
    public function show(MiniCourse $course): JsonResponse
    {
        $this->authorize('view', $course);

        $course->load([
            'steps' => fn ($q) => $q->orderBy('sort_order'),
            'steps.resource',
            'steps.provider',
            'steps.program',
            'creator',
            'versions' => fn ($q) => $q->orderByDesc('version_number')->limit(5),
        ]);

        $course->loadCount(['enrollments', 'steps']);

        return response()->json($course);
    }

    /**
     * Create a new course.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'objectives' => 'nullable|array',
            'rationale' => 'nullable|string',
            'expected_experience' => 'nullable|string',
            'course_type' => 'required|in:intervention,enrichment,skill_building,wellness,academic,behavioral',
            'target_grades' => 'nullable|array',
            'target_risk_levels' => 'nullable|array',
            'target_needs' => 'nullable|array',
            'estimated_duration_minutes' => 'nullable|integer|min:1|max:480',
            'is_template' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $user = auth()->user();

        $course = MiniCourse::create([
            ...$validated,
            'org_id' => $user->org_id,
            'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
            'status' => MiniCourse::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        AuditLog::log('create', $course);

        return response()->json([
            'success' => true,
            'course' => $course,
        ], 201);
    }

    /**
     * Update a course.
     */
    public function update(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10',
            'objectives' => 'nullable|array',
            'rationale' => 'nullable|string',
            'expected_experience' => 'nullable|string',
            'course_type' => 'sometimes|in:intervention,enrichment,skill_building,wellness,academic,behavioral',
            'target_grades' => 'nullable|array',
            'target_risk_levels' => 'nullable|array',
            'target_needs' => 'nullable|array',
            'estimated_duration_minutes' => 'nullable|integer|min:1|max:480',
            'is_template' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $oldValues = $course->only(array_keys($validated));

        $course->update($validated);

        AuditLog::log('update', $course, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'course' => $course->fresh(),
        ]);
    }

    /**
     * Delete a course.
     */
    public function destroy(MiniCourse $course): JsonResponse
    {
        $this->authorize('delete', $course);

        AuditLog::log('delete', $course);

        $course->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Duplicate a course.
     */
    public function duplicate(MiniCourse $course): JsonResponse
    {
        $this->authorize('view', $course);

        $newCourse = $course->duplicate();

        AuditLog::log('create', $newCourse, null, ['source' => 'duplicate', 'original_id' => $course->id]);

        return response()->json([
            'success' => true,
            'course' => $newCourse->load(['steps' => fn ($q) => $q->orderBy('sort_order')]),
        ], 201);
    }

    /**
     * Publish a course (make it active).
     */
    public function publish(MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        if ($course->steps()->count() === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot publish a course without steps.',
            ], 422);
        }

        $course->publish();

        AuditLog::log('update', $course, ['status' => $course->getOriginal('status')], ['status' => 'active', 'published_at' => $course->published_at]);

        return response()->json([
            'success' => true,
            'course' => $course,
            'version' => $course->currentVersion,
        ]);
    }

    /**
     * Archive a course.
     */
    public function archive(MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $course->archive();

        AuditLog::log('update', $course, ['status' => 'active'], ['status' => 'archived']);

        return response()->json([
            'success' => true,
            'course' => $course,
        ]);
    }

    /**
     * Generate a course using AI.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'signals' => 'nullable|array',
        ]);

        $student = \App\Models\Student::findOrFail($validated['student_id']);

        // Verify org access
        if ($student->org_id !== auth()->user()->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $course = $this->generationService->generateFromContext(
            $student,
            $validated['signals'] ?? []
        );

        if (!$course) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate course. Please try again.',
            ], 500);
        }

        AuditLog::log('create', $course, null, ['source' => 'ai_generated', 'student_id' => $student->id]);

        return response()->json([
            'success' => true,
            'course' => $course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]),
        ], 201);
    }

    /**
     * Get AI suggestions for improving a course.
     */
    public function suggestEdits(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $student = null;
        if ($request->has('student_id')) {
            $student = \App\Models\Student::find($request->student_id);
        }

        $suggestions = $this->generationService->suggestCourseEdits($course, $student);

        return response()->json($suggestions);
    }

    /**
     * Get version history for a course.
     */
    public function versions(MiniCourse $course): JsonResponse
    {
        $this->authorize('view', $course);

        $versions = $course->versions()
            ->with('creator')
            ->orderByDesc('version_number')
            ->paginate(10);

        return response()->json($versions);
    }

    /**
     * Restore a previous version.
     */
    public function restoreVersion(MiniCourse $course, int $versionId): JsonResponse
    {
        $this->authorize('update', $course);

        $version = $course->versions()->findOrFail($versionId);

        $version->restoreAsCurrent();

        AuditLog::log('update', $course, null, ['action' => 'version_restored', 'version_id' => $versionId]);

        return response()->json([
            'success' => true,
            'course' => $course->fresh()->load(['steps' => fn ($q) => $q->orderBy('sort_order')]),
        ]);
    }
}
