<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Provider;
use App\Models\Program;
use App\Models\Resource;
use Livewire\Component;

class MiniCourseEditor extends Component
{
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
        if (!$this->course || $this->course->steps->isEmpty()) {
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
