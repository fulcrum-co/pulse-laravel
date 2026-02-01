<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Services\MiniCourseGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MiniCourseStepController extends Controller
{
    public function __construct(
        protected MiniCourseGenerationService $generationService
    ) {}

    /**
     * List all steps for a course.
     */
    public function index(MiniCourse $course): JsonResponse
    {
        $this->authorize('view', $course);

        $steps = $course->steps()
            ->with(['resource', 'provider', 'program'])
            ->orderBy('sort_order')
            ->get();

        return response()->json($steps);
    }

    /**
     * Create a new step.
     */
    public function store(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'step_type' => 'required|in:content,reflection,action,practice,human_connection,assessment,checkpoint',
            'title' => 'required|string|min:1|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'content_type' => 'required|in:text,video,document,link,embedded,interactive',
            'content_data' => 'nullable|array',
            'resource_id' => 'nullable|exists:resources,id',
            'provider_id' => 'nullable|exists:providers,id',
            'program_id' => 'nullable|exists:programs,id',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'completion_criteria' => 'nullable|array',
            'feedback_prompt' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Set sort order if not provided
        if (! isset($validated['sort_order'])) {
            $maxSort = $course->steps()->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxSort + 1;
        }

        $step = $course->steps()->create($validated);

        AuditLog::log('create', $step);

        return response()->json([
            'success' => true,
            'step' => $step->load(['resource', 'provider', 'program']),
        ], 201);
    }

    /**
     * Get a single step.
     */
    public function show(MiniCourse $course, MiniCourseStep $step): JsonResponse
    {
        $this->authorize('view', $course);

        if ($step->mini_course_id !== $course->id) {
            return response()->json(['error' => 'Step not found in this course'], 404);
        }

        $step->load(['resource', 'provider', 'program']);

        return response()->json($step);
    }

    /**
     * Update a step.
     */
    public function update(Request $request, MiniCourse $course, MiniCourseStep $step): JsonResponse
    {
        $this->authorize('update', $course);

        if ($step->mini_course_id !== $course->id) {
            return response()->json(['error' => 'Step not found in this course'], 404);
        }

        $validated = $request->validate([
            'step_type' => 'sometimes|in:content,reflection,action,practice,human_connection,assessment,checkpoint',
            'title' => 'sometimes|string|min:1|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'content_type' => 'sometimes|in:text,video,document,link,embedded,interactive',
            'content_data' => 'nullable|array',
            'resource_id' => 'nullable|exists:resources,id',
            'provider_id' => 'nullable|exists:providers,id',
            'program_id' => 'nullable|exists:programs,id',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'completion_criteria' => 'nullable|array',
            'feedback_prompt' => 'nullable|string',
        ]);

        $oldValues = $step->only(array_keys($validated));

        $step->update($validated);

        AuditLog::log('update', $step, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'step' => $step->fresh()->load(['resource', 'provider', 'program']),
        ]);
    }

    /**
     * Delete a step.
     */
    public function destroy(MiniCourse $course, MiniCourseStep $step): JsonResponse
    {
        $this->authorize('update', $course);

        if ($step->mini_course_id !== $course->id) {
            return response()->json(['error' => 'Step not found in this course'], 404);
        }

        AuditLog::log('delete', $step);

        $step->delete();

        // Reorder remaining steps
        $course->steps()->orderBy('sort_order')->get()->each(function ($s, $index) {
            $s->update(['sort_order' => $index + 1]);
        });

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reorder steps.
     */
    public function reorder(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:mini_course_steps,id',
        ]);

        foreach ($validated['order'] as $index => $stepId) {
            MiniCourseStep::where('id', $stepId)
                ->where('mini_course_id', $course->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'steps' => $course->steps()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Generate content for a step using AI.
     */
    public function generateContent(Request $request, MiniCourse $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'step_type' => 'required|in:content,reflection,action,practice,assessment',
            'topic' => 'required|string|min:3',
            'context' => 'nullable|array',
        ]);

        $content = $this->generationService->generateStepContent(
            $validated['step_type'],
            $validated['topic'],
            $validated['context'] ?? ['course_title' => $course->title, 'course_type' => $course->course_type]
        );

        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }
}
