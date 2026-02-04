<?php

namespace App\Livewire;

use App\Jobs\GenerateCourseJob;
use App\Models\MiniCourse;
use App\Services\CourseOrchestrator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CourseGenerator extends Component
{
    // Form inputs
    public string $topic = '';

    public array $targetGrades = [];

    public array $targetRiskLevels = [];

    public int $targetDurationMinutes = 30;

    public string $courseType = 'skill_building';

    // State
    public bool $isGenerating = false;

    public ?string $generationJobId = null;

    public ?int $generatedCourseId = null;

    public ?string $error = null;

    // Course preview after generation
    public ?array $coursePreview = null;

    protected $rules = [
        'topic' => 'required|string|min:5|max:500',
        'targetGrades' => 'array',
        'targetGrades.*' => 'string',
        'targetRiskLevels' => 'array',
        'targetRiskLevels.*' => 'string',
        'targetDurationMinutes' => 'required|integer|min:5|max:120',
        'courseType' => 'required|string|in:intervention,enrichment,skill_building,wellness,academic,behavioral',
    ];

    protected $messages = [
        'topic.required' => 'Please enter a topic for the course.',
        'topic.min' => 'Topic should be at least 5 characters.',
        'targetDurationMinutes.min' => 'Course should be at least 5 minutes.',
        'targetDurationMinutes.max' => 'Course should not exceed 120 minutes.',
    ];

    public function mount(): void
    {
        // Check for any pending generation
        $this->checkGenerationStatus();
    }

    /**
     * Generate a new course.
     */
    public function generate(): void
    {
        $this->validate();

        $this->error = null;
        $this->isGenerating = true;
        $this->coursePreview = null;
        $this->generatedCourseId = null;

        try {
            $user = auth()->user();
            $orgId = $user->org_id;

            // For quick feedback, generate synchronously if Claude is fast
            // For production, this would be queued
            $orchestrator = app(CourseOrchestrator::class);

            $course = $orchestrator->generateCourse([
                'topic' => $this->topic,
                'orgId' => $orgId,
                'targetGrades' => $this->targetGrades,
                'targetRiskLevels' => $this->targetRiskLevels,
                'targetDurationMinutes' => $this->targetDurationMinutes,
                'courseType' => $this->courseType,
                'createdBy' => $user->id,
            ]);

            $this->generatedCourseId = $course->id;
            $this->loadCoursePreview($course);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course generated successfully! Review and publish when ready.',
            ]);
        } catch (\Exception $e) {
            $this->error = 'Failed to generate course. Please try again.';
            report($e);
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Generate course in background (for long-running generations).
     */
    public function generateAsync(): void
    {
        $this->validate();

        $this->error = null;
        $this->isGenerating = true;
        $this->coursePreview = null;

        $user = auth()->user();
        $jobId = uniqid('course_gen_');

        // Store generation request in cache
        Cache::put("course_generation_{$jobId}", [
            'status' => 'pending',
            'user_id' => $user->id,
            'org_id' => $user->org_id,
            'params' => [
                'topic' => $this->topic,
                'targetGrades' => $this->targetGrades,
                'targetRiskLevels' => $this->targetRiskLevels,
                'targetDurationMinutes' => $this->targetDurationMinutes,
                'courseType' => $this->courseType,
            ],
            'started_at' => now()->toIso8601String(),
        ], 3600);

        $this->generationJobId = $jobId;

        // Dispatch background job
        GenerateCourseJob::dispatch(
            jobId: $jobId,
            params: [
                'topic' => $this->topic,
                'orgId' => $user->org_id,
                'targetGrades' => $this->targetGrades,
                'targetRiskLevels' => $this->targetRiskLevels,
                'targetDurationMinutes' => $this->targetDurationMinutes,
                'courseType' => $this->courseType,
                'createdBy' => $user->id,
            ]
        );

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Course generation started. This may take a moment...',
        ]);
    }

    /**
     * Check status of background generation.
     */
    public function checkGenerationStatus(): void
    {
        if (! $this->generationJobId) {
            return;
        }

        $status = Cache::get("course_generation_{$this->generationJobId}");

        if (! $status) {
            $this->generationJobId = null;
            $this->isGenerating = false;

            return;
        }

        if ($status['status'] === 'completed' && isset($status['course_id'])) {
            $this->generatedCourseId = $status['course_id'];
            $course = MiniCourse::with('steps')->find($status['course_id']);
            if ($course) {
                $this->loadCoursePreview($course);
            }
            $this->isGenerating = false;
            $this->generationJobId = null;
            Cache::forget("course_generation_{$this->generationJobId}");

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course generated successfully!',
            ]);
        } elseif ($status['status'] === 'failed') {
            $this->error = $status['error'] ?? 'Generation failed. Please try again.';
            $this->isGenerating = false;
            $this->generationJobId = null;
            Cache::forget("course_generation_{$this->generationJobId}");
        }
    }

    /**
     * Load course data for preview.
     */
    protected function loadCoursePreview(MiniCourse $course): void
    {
        $course->load('steps.resource');

        $this->coursePreview = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'objectives' => $course->objectives ?? [],
            'rationale' => $course->rationale,
            'course_type' => $course->course_type,
            'duration' => $course->estimated_duration_minutes,
            'difficulty' => $course->difficulty_level,
            'status' => $course->status,
            'approval_status' => $course->approval_status,
            'steps' => $course->steps->map(fn ($step) => [
                'id' => $step->id,
                'title' => $step->title,
                'description' => $step->description,
                'step_type' => $step->step_type,
                'content_type' => $step->content_type,
                'duration' => $step->estimated_duration_minutes,
                'resource' => $step->resource ? [
                    'id' => $step->resource->id,
                    'title' => $step->resource->title,
                    'type' => $step->resource->resource_type,
                ] : null,
            ])->toArray(),
        ];
    }

    /**
     * Edit the generated course.
     */
    public function editCourse(): void
    {
        if ($this->generatedCourseId) {
            $this->redirect(route('resources.courses.edit', $this->generatedCourseId));
        }
    }

    /**
     * Start a new generation.
     */
    public function startNew(): void
    {
        $this->topic = '';
        $this->targetGrades = [];
        $this->targetRiskLevels = [];
        $this->targetDurationMinutes = 30;
        $this->courseType = 'skill_building';
        $this->coursePreview = null;
        $this->generatedCourseId = null;
        $this->error = null;
    }

    /**
     * Get available course types.
     */
    public function getCourseTypesProperty(): array
    {
        return MiniCourse::getCourseTypes();
    }

    /**
     * Get available grade options.
     */
    public function getGradeOptionsProperty(): array
    {
        return [
            'K' => 'Kindergarten',
            '1' => 'Grade 1',
            '2' => 'Grade 2',
            '3' => 'Grade 3',
            '4' => 'Grade 4',
            '5' => 'Grade 5',
            '6' => 'Grade 6',
            '7' => 'Grade 7',
            '8' => 'Grade 8',
            '9' => 'Grade 9',
            '10' => 'Grade 10',
            '11' => 'Grade 11',
            '12' => 'Grade 12',
        ];
    }

    /**
     * Get available risk level options.
     */
    public function getRiskLevelOptionsProperty(): array
    {
        return [
            'low' => 'Low Risk',
            'moderate' => 'Moderate Risk',
            'high' => 'High Risk',
            'crisis' => 'Crisis',
        ];
    }

    /**
     * Get duration options.
     */
    public function getDurationOptionsProperty(): array
    {
        return [
            5 => '5 minutes (Quick)',
            15 => '15 minutes (Short)',
            30 => '30 minutes (Standard)',
            45 => '45 minutes (Extended)',
            60 => '60 minutes (Full)',
            90 => '90 minutes (Comprehensive)',
        ];
    }

    public function render()
    {
        return view('livewire.course-generator', [
            'courseTypes' => $this->courseTypes,
            'gradeOptions' => $this->gradeOptions,
            'riskLevelOptions' => $this->riskLevelOptions,
            'durationOptions' => $this->durationOptions,
        ]);
    }
}
