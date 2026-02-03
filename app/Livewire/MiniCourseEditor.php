<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Services\CourseContentAIService;
use Livewire\Component;
use Livewire\WithFileUploads;

class MiniCourseEditor extends Component
{
    use WithFileUploads;

    public ?MiniCourse $course = null;

    public bool $isNew = true;

    // Course form data
    public string $title = '';

    public string $description = '';

    public array $objectives = [];

    public string $rationale = '';

    public string $expectedExperience = '';

    public string $courseType = 'intervention';

    public array $targetGrades = [];

    public array $targetRiskLevels = [];

    public array $targetNeeds = [];

    public ?int $estimatedDuration = null;

    public bool $isTemplate = false;

    public bool $isPublic = false;

    // Step editing
    public ?int $editingStepId = null;

    public array $stepForm = [];

    // New objective input
    public string $newObjective = '';

    // Modals
    public bool $showStepModal = false;

    public bool $showPreview = false;

    public bool $showPublishConfirm = false;

    // AI Assistant
    public bool $showAIPanel = false;

    public bool $aiGenerating = false;

    public string $aiTopic = '';

    public string $aiAudience = 'learners';

    public ?string $aiGradeLevel = null;

    public int $aiDurationMinutes = 30;

    public array $aiSuggestions = [];

    public ?string $aiError = null;

    // Document upload for AI extraction
    public $uploadedDocument = null;

    public bool $processingDocument = false;

    protected $rules = [
        'title' => 'required|string|min:3|max:255',
        'description' => 'required|string|min:10',
        'rationale' => 'nullable|string',
        'expectedExperience' => 'nullable|string',
        'courseType' => 'required|in:intervention,enrichment,skill_building,wellness,academic,behavioral',
        'estimatedDuration' => 'nullable|integer|min:1|max:480',
    ];

    public function mount(?MiniCourse $course = null): void
    {
        if ($course && $course->exists) {
            $this->course = $course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);
            $this->isNew = false;
            $this->fillFromCourse();
        }
    }

    protected function fillFromCourse(): void
    {
        $this->title = $this->course->title;
        $this->description = $this->course->description ?? '';
        $this->objectives = $this->course->objectives ?? [];
        $this->rationale = $this->course->rationale ?? '';
        $this->expectedExperience = $this->course->expected_experience ?? '';
        $this->courseType = $this->course->course_type;
        $this->targetGrades = $this->course->target_grades ?? [];
        $this->targetRiskLevels = $this->course->target_risk_levels ?? [];
        $this->targetNeeds = $this->course->target_needs ?? [];
        $this->estimatedDuration = $this->course->estimated_duration_minutes;
        $this->isTemplate = $this->course->is_template;
        $this->isPublic = $this->course->is_public;
    }

    public function addObjective(): void
    {
        if (trim($this->newObjective)) {
            $this->objectives[] = trim($this->newObjective);
            $this->newObjective = '';
        }
    }

    public function removeObjective(int $index): void
    {
        unset($this->objectives[$index]);
        $this->objectives = array_values($this->objectives);
    }

    public function saveCourse(): void
    {
        $this->validate();

        $user = auth()->user();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'objectives' => $this->objectives,
            'rationale' => $this->rationale,
            'expected_experience' => $this->expectedExperience,
            'course_type' => $this->courseType,
            'target_grades' => $this->targetGrades,
            'target_risk_levels' => $this->targetRiskLevels,
            'target_needs' => $this->targetNeeds,
            'estimated_duration_minutes' => $this->estimatedDuration,
            'is_template' => $this->isTemplate,
            'is_public' => $this->isPublic,
        ];

        if ($this->isNew) {
            $this->course = MiniCourse::create([
                ...$data,
                'org_id' => $user->org_id,
                'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
                'status' => MiniCourse::STATUS_DRAFT,
                'created_by' => $user->id,
            ]);
            $this->isNew = false;

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course created successfully!',
            ]);
        } else {
            $this->course->update($data);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course saved successfully!',
            ]);
        }
    }

    public function openStepModal(?int $stepId = null): void
    {
        $this->editingStepId = $stepId;

        if ($stepId) {
            $step = MiniCourseStep::find($stepId);
            $this->stepForm = [
                'step_type' => $step->step_type,
                'title' => $step->title,
                'description' => $step->description,
                'instructions' => $step->instructions,
                'content_type' => $step->content_type,
                'content_data' => $step->content_data ?? [],
                'estimated_duration_minutes' => $step->estimated_duration_minutes,
                'is_required' => $step->is_required,
                'feedback_prompt' => $step->feedback_prompt,
                'resource_id' => $step->resource_id,
                'provider_id' => $step->provider_id,
                'program_id' => $step->program_id,
            ];
        } else {
            $this->stepForm = [
                'step_type' => MiniCourseStep::TYPE_CONTENT,
                'title' => '',
                'description' => '',
                'instructions' => '',
                'content_type' => MiniCourseStep::CONTENT_TEXT,
                'content_data' => [],
                'estimated_duration_minutes' => 5,
                'is_required' => true,
                'feedback_prompt' => '',
                'resource_id' => null,
                'provider_id' => null,
                'program_id' => null,
            ];
        }

        $this->showStepModal = true;
    }

    public function closeStepModal(): void
    {
        $this->showStepModal = false;
        $this->editingStepId = null;
        $this->stepForm = [];
    }

    public function saveStep(): void
    {
        $this->validate([
            'stepForm.title' => 'required|string|min:1',
            'stepForm.step_type' => 'required|string',
            'stepForm.content_type' => 'required|string',
        ]);

        if ($this->editingStepId) {
            $step = MiniCourseStep::find($this->editingStepId);
            $step->update($this->stepForm);
        } else {
            $maxSort = $this->course->steps()->max('sort_order') ?? 0;

            $this->course->steps()->create([
                ...$this->stepForm,
                'sort_order' => $maxSort + 1,
            ]);

            // Refresh course steps
            $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);
        }

        $this->closeStepModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->editingStepId ? 'Step updated!' : 'Step added!',
        ]);
    }

    public function deleteStep(int $stepId): void
    {
        MiniCourseStep::find($stepId)?->delete();
        $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Step deleted.',
        ]);
    }

    public function moveStepUp(int $stepId): void
    {
        $step = MiniCourseStep::find($stepId);
        $step?->moveUp();
        $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);
    }

    public function moveStepDown(int $stepId): void
    {
        $step = MiniCourseStep::find($stepId);
        $step?->moveDown();
        $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);
    }

    public function publish(): void
    {
        if (! $this->course || $this->course->steps->isEmpty()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Add at least one step before publishing.',
            ]);

            return;
        }

        $this->course->publish();
        $this->showPublishConfirm = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Course published successfully!',
        ]);
    }

    public function getAvailableResourcesProperty()
    {
        $user = auth()->user();

        return Resource::forOrganization($user->org_id)->active()->orderBy('title')->get();
    }

    public function getAvailableProvidersProperty()
    {
        $user = auth()->user();

        return Provider::where('org_id', $user->org_id)->active()->orderBy('name')->get();
    }

    public function getAvailableProgramsProperty()
    {
        $user = auth()->user();

        return Program::where('org_id', $user->org_id)->active()->orderBy('name')->get();
    }

    public function getStepTypesProperty(): array
    {
        return [
            MiniCourseStep::TYPE_CONTENT => 'Content',
            MiniCourseStep::TYPE_REFLECTION => 'Reflection',
            MiniCourseStep::TYPE_ACTION => 'Action',
            MiniCourseStep::TYPE_PRACTICE => 'Practice',
            MiniCourseStep::TYPE_HUMAN_CONNECTION => 'Human Connection',
            MiniCourseStep::TYPE_ASSESSMENT => 'Assessment',
            MiniCourseStep::TYPE_CHECKPOINT => 'Checkpoint',
        ];
    }

    public function getContentTypesProperty(): array
    {
        return [
            MiniCourseStep::CONTENT_TEXT => 'Text',
            MiniCourseStep::CONTENT_VIDEO => 'Video',
            MiniCourseStep::CONTENT_DOCUMENT => 'Document',
            MiniCourseStep::CONTENT_LINK => 'Link',
            MiniCourseStep::CONTENT_EMBEDDED => 'Embedded',
            MiniCourseStep::CONTENT_INTERACTIVE => 'Interactive',
        ];
    }

    public function getCourseTypesProperty(): array
    {
        return [
            MiniCourse::TYPE_INTERVENTION => 'Intervention',
            MiniCourse::TYPE_ENRICHMENT => 'Enrichment',
            MiniCourse::TYPE_SKILL_BUILDING => 'Skill Building',
            MiniCourse::TYPE_WELLNESS => 'Wellness',
            MiniCourse::TYPE_ACADEMIC => 'Academic',
            MiniCourse::TYPE_BEHAVIORAL => 'Behavioral',
        ];
    }

    // ============================================
    // AI ASSISTANT METHODS
    // ============================================

    public function toggleAIPanel(): void
    {
        $this->showAIPanel = ! $this->showAIPanel;
        $this->aiError = null;
    }

    /**
     * Generate a complete course draft using AI.
     */
    public function generateFullCourse(): void
    {
        if (empty($this->aiTopic)) {
            $this->aiError = 'Please enter a topic for the course.';

            return;
        }

        $this->aiGenerating = true;
        $this->aiError = null;

        try {
            $aiService = app(CourseContentAIService::class);

            $result = $aiService->generateCompleteCourse([
                'topic' => $this->aiTopic,
                'audience' => $this->aiAudience,
                'grade_level' => $this->aiGradeLevel,
                'course_type' => $this->courseType,
                'duration_minutes' => $this->aiDurationMinutes,
                'objectives' => $this->objectives,
            ]);

            if ($result['success']) {
                $this->applyAICourse($result['course']);
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'AI generated course draft! Review and customize.',
                ]);
            } else {
                $this->aiError = $result['error'] ?? 'Failed to generate course.';
            }
        } catch (\Exception $e) {
            $this->aiError = 'An error occurred while generating the course.';
            \Log::error('AI course generation failed', ['error' => $e->getMessage()]);
        } finally {
            $this->aiGenerating = false;
        }
    }

    /**
     * Apply AI-generated course data to the form.
     */
    protected function applyAICourse(array $courseData): void
    {
        // Apply course metadata
        $this->title = $courseData['title'] ?? $this->title;
        $this->description = $courseData['description'] ?? $this->description;
        $this->objectives = $courseData['objectives'] ?? $this->objectives;
        $this->rationale = $courseData['rationale'] ?? '';
        $this->expectedExperience = $courseData['expected_experience'] ?? '';
        $this->courseType = $courseData['course_type'] ?? $this->courseType;
        $this->targetNeeds = $courseData['target_needs'] ?? [];
        $this->estimatedDuration = $courseData['estimated_duration_minutes'] ?? $this->aiDurationMinutes;

        // Save the course first if it's new
        if ($this->isNew) {
            $this->saveCourse();
        }

        // Create steps if provided
        if (! empty($courseData['steps']) && $this->course) {
            // Clear existing steps if this is AI generation
            $this->course->steps()->delete();

            foreach ($courseData['steps'] as $index => $stepData) {
                $this->course->steps()->create([
                    'sort_order' => $index + 1,
                    'step_type' => $stepData['step_type'] ?? MiniCourseStep::TYPE_CONTENT,
                    'title' => $stepData['title'] ?? 'Step '.($index + 1),
                    'description' => $stepData['description'] ?? null,
                    'instructions' => $stepData['instructions'] ?? null,
                    'content_type' => $stepData['content_type'] ?? MiniCourseStep::CONTENT_TEXT,
                    'content_data' => $stepData['content_data'] ?? [],
                    'estimated_duration_minutes' => $stepData['duration'] ?? 5,
                    'is_required' => $stepData['is_required'] ?? true,
                    'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
                ]);
            }

            // Refresh steps
            $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);
        }

        // Save course updates
        if (! $this->isNew) {
            $this->saveCourse();
        }
    }

    /**
     * Generate AI content for a specific section.
     */
    public function generateSection(string $sectionType): void
    {
        $this->aiGenerating = true;
        $this->aiError = null;

        try {
            $aiService = app(CourseContentAIService::class);
            $topic = $this->title ?: $this->aiTopic ?: 'the course topic';

            $context = [
                'course_type' => $this->courseType,
                'audience' => $this->aiAudience,
                'grade_level' => $this->aiGradeLevel,
                'objectives' => $this->objectives,
            ];

            $result = match ($sectionType) {
                'introduction' => $aiService->generateIntroduction($topic, $context),
                'content' => $aiService->generateContentSection($topic, $this->objectives, $context),
                'reflection' => $aiService->generateReflectionPrompts($topic, $context),
                'assessment' => $aiService->generateAssessment($this->objectives, 'quiz', $context),
                'action' => $aiService->generateActionPlan($topic, $context),
                default => ['success' => false, 'error' => 'Unknown section type'],
            };

            if ($result['success']) {
                $this->aiSuggestions = $result[$sectionType] ?? $result['data'] ?? $result;
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => ucfirst($sectionType).' content generated! Click to apply.',
                ]);
            } else {
                $this->aiError = $result['error'] ?? 'Failed to generate content.';
            }
        } catch (\Exception $e) {
            $this->aiError = 'An error occurred while generating content.';
            \Log::error('AI section generation failed', ['error' => $e->getMessage()]);
        } finally {
            $this->aiGenerating = false;
        }
    }

    /**
     * Apply AI suggestions to the form.
     */
    public function applySuggestion(string $field, $value): void
    {
        match ($field) {
            'title' => $this->title = $value,
            'description' => $this->description = $value,
            'rationale' => $this->rationale = $value,
            'expectedExperience' => $this->expectedExperience = $value,
            'objectives' => $this->objectives = is_array($value) ? $value : [$value],
            default => null,
        };

        $this->aiSuggestions = [];
    }

    /**
     * Add a step from AI suggestions.
     */
    public function addAIStep(array $stepData): void
    {
        if (! $this->course) {
            $this->saveCourse();
        }

        if ($this->course) {
            $maxSort = $this->course->steps()->max('sort_order') ?? 0;

            $this->course->steps()->create([
                'sort_order' => $maxSort + 1,
                'step_type' => $stepData['step_type'] ?? MiniCourseStep::TYPE_CONTENT,
                'title' => $stepData['title'] ?? 'New Step',
                'description' => $stepData['description'] ?? null,
                'instructions' => $stepData['instructions'] ?? null,
                'content_type' => $stepData['content_type'] ?? MiniCourseStep::CONTENT_TEXT,
                'content_data' => $stepData['content_data'] ?? [],
                'estimated_duration_minutes' => $stepData['duration'] ?? 5,
                'is_required' => $stepData['is_required'] ?? true,
                'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
            ]);

            $this->course->load(['steps' => fn ($q) => $q->orderBy('sort_order')]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Step added from AI suggestion!',
            ]);
        }
    }

    /**
     * Process uploaded document and extract course structure.
     */
    public function processDocument(): void
    {
        if (! $this->uploadedDocument) {
            $this->aiError = 'Please upload a document first.';

            return;
        }

        $this->validate([
            'uploadedDocument' => 'required|file|mimes:txt,pdf,doc,docx|max:5120',
        ]);

        $this->processingDocument = true;
        $this->aiError = null;

        try {
            // Extract text from document
            $text = $this->extractTextFromDocument($this->uploadedDocument);

            if (empty($text)) {
                $this->aiError = 'Could not extract text from the document.';

                return;
            }

            $aiService = app(CourseContentAIService::class);

            $result = $aiService->extractCourseFromDocument($text, [
                'course_type' => $this->courseType,
                'audience' => $this->aiAudience,
                'grade_level' => $this->aiGradeLevel,
            ]);

            if ($result['success']) {
                $this->applyAICourse($result['course']);
                $this->uploadedDocument = null;
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Course extracted from document! Review and customize.',
                ]);
            } else {
                $this->aiError = $result['error'] ?? 'Failed to extract course from document.';
            }
        } catch (\Exception $e) {
            $this->aiError = 'An error occurred while processing the document.';
            \Log::error('Document processing failed', ['error' => $e->getMessage()]);
        } finally {
            $this->processingDocument = false;
        }
    }

    /**
     * Extract text content from uploaded document.
     */
    protected function extractTextFromDocument($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'txt') {
            return file_get_contents($file->getRealPath());
        }

        // For PDF and DOC files, we'll use a simple approach
        // In production, you'd want to use proper libraries like Smalot\PdfParser or PhpWord
        if ($extension === 'pdf') {
            // Basic PDF text extraction - you may want to use a proper library
            $content = file_get_contents($file->getRealPath());
            // Strip binary content and try to get text
            $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $content);

            return $text ?: '';
        }

        // For now, return empty for unsupported formats
        // In production, implement proper document parsing
        return '';
    }

    /**
     * Generate AI content for the currently editing step.
     */
    public function generateStepContent(): void
    {
        if (empty($this->stepForm['title'])) {
            $this->aiError = 'Please enter a step title first.';

            return;
        }

        $this->aiGenerating = true;
        $this->aiError = null;

        try {
            $aiService = app(CourseContentAIService::class);

            $stepType = $this->stepForm['step_type'] ?? MiniCourseStep::TYPE_CONTENT;
            $topic = $this->stepForm['title'];

            $context = [
                'course_title' => $this->title,
                'course_type' => $this->courseType,
                'objectives' => $this->objectives,
                'grade_level' => $this->aiGradeLevel,
            ];

            // Generate content based on step type
            $result = match ($stepType) {
                MiniCourseStep::TYPE_REFLECTION => $aiService->generateReflectionPrompts($topic, $context),
                MiniCourseStep::TYPE_ASSESSMENT => $aiService->generateAssessment($this->objectives, 'quiz', ['num_questions' => 3]),
                MiniCourseStep::TYPE_ACTION => $aiService->generateActionPlan($topic, $context),
                default => $aiService->generateContentSection($topic, $this->objectives, $context),
            };

            if ($result['success']) {
                $data = $result['content'] ?? $result['reflection'] ?? $result['assessment'] ?? $result['action_plan'] ?? [];

                // Apply to step form
                if (isset($data['body'])) {
                    $this->stepForm['content_data']['body'] = $data['body'];
                }
                if (isset($data['key_points'])) {
                    $this->stepForm['content_data']['key_points'] = $data['key_points'];
                }
                if (isset($data['prompts'])) {
                    $this->stepForm['content_data']['prompts'] = $data['prompts'];
                }
                if (isset($data['questions'])) {
                    $this->stepForm['content_data']['questions'] = $data['questions'];
                }
                if (isset($data['introduction'])) {
                    $this->stepForm['description'] = $data['introduction'];
                }

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Content generated for step!',
                ]);
            } else {
                $this->aiError = $result['error'] ?? 'Failed to generate step content.';
            }
        } catch (\Exception $e) {
            $this->aiError = 'An error occurred while generating content.';
            \Log::error('AI step content generation failed', ['error' => $e->getMessage()]);
        } finally {
            $this->aiGenerating = false;
        }
    }

    /**
     * Get AI suggestions for improving current content.
     */
    public function getAISuggestions(string $field): void
    {
        $content = match ($field) {
            'title' => $this->title,
            'description' => $this->description,
            'rationale' => $this->rationale,
            'expectedExperience' => $this->expectedExperience,
            default => '',
        };

        if (empty($content)) {
            $this->aiError = 'Please enter some content first.';

            return;
        }

        $this->aiGenerating = true;

        try {
            $aiService = app(CourseContentAIService::class);

            $result = $aiService->getInlineSuggestions($content, $field, [
                'course_title' => $this->title,
                'course_type' => $this->courseType,
            ]);

            if ($result['success']) {
                $this->aiSuggestions = $result['data'];
            } else {
                $this->aiError = $result['error'] ?? 'Failed to get suggestions.';
            }
        } catch (\Exception $e) {
            $this->aiError = 'An error occurred.';
        } finally {
            $this->aiGenerating = false;
        }
    }

    public function clearAISuggestions(): void
    {
        $this->aiSuggestions = [];
        $this->aiError = null;
    }

    public function render()
    {
        return view('livewire.mini-course-editor', [
            'stepTypes' => $this->stepTypes,
            'contentTypes' => $this->contentTypes,
            'courseTypes' => $this->courseTypes,
            'availableResources' => $this->availableResources,
            'availableProviders' => $this->availableProviders,
            'availablePrograms' => $this->availablePrograms,
        ])->layout('layouts.dashboard', ['title' => 'Mini-Course Editor']);
    }
}
