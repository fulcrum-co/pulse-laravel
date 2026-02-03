<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseStepProgress;
use App\Models\Participant;
use Livewire\Component;

class MiniCourseViewer extends Component
{
    public MiniCourse $course;

    public ?MiniCourseEnrollment $enrollment = null;

    public ?MiniCourseStep $currentStep = null;

    public bool $showRationale = false;

    public bool $previewMode = false;

    // For staff viewing participant progress
    public ?int $viewingLearnerId = null;

    public function mount(MiniCourse $course, ?int $participantId = null): void
    {
        $this->course = $course->load(['steps' => fn ($q) => $q->orderBy('sort_order'), 'creator']);
        $this->viewingLearnerId = $participantId;

        // Check if current user has an enrollment
        $user = auth()->user();
        if ($user && $user->participant) {
            $this->enrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
                ->where('participant_id', $user->participant->id)
                ->first();

            if ($this->enrollment) {
                $this->currentStep = $this->enrollment->currentStep ?? $this->course->steps->first();
            }
        }

        // If viewing as staff for a specific participant
        if ($participantId) {
            $this->enrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
                ->where('participant_id', $participantId)
                ->first();
        }

        // Default to first step if no enrollment
        if (! $this->currentStep && $this->course->steps->isNotEmpty()) {
            $this->currentStep = $this->course->steps->first();
        }
    }

    public function selectStep(int $stepId): void
    {
        $this->currentStep = $this->course->steps->find($stepId);
    }

    public function toggleRationale(): void
    {
        $this->showRationale = ! $this->showRationale;
    }

    public function completeCurrentStep(): void
    {
        // Handle preview mode - just advance to next step without tracking
        if ($this->previewMode) {
            $this->advanceToNextStep();

            return;
        }

        if (! $this->enrollment || ! $this->currentStep) {
            return;
        }

        // Mark step as completed
        $progress = MiniCourseStepProgress::firstOrCreate([
            'enrollment_id' => $this->enrollment->id,
            'step_id' => $this->currentStep->id,
        ]);

        $progress->update([
            'status' => MiniCourseStepProgress::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Recalculate enrollment progress
        $this->enrollment->recalculateProgress();

        // Move to next step using course's steps collection (reset keys with values())
        $steps = $this->course->steps->values();
        $currentIndex = $steps->search(fn ($s) => $s->id === $this->currentStep->id);

        if ($currentIndex !== false && $currentIndex < $steps->count() - 1) {
            $nextStep = $steps->get($currentIndex + 1);
            $this->currentStep = $nextStep;
            $this->enrollment->update(['current_step_id' => $nextStep->id]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Step completed!',
        ]);
    }

    public function advanceToNextStep(): void
    {
        if (! $this->currentStep) {
            return;
        }

        // Find next step using the course's steps collection (reset keys with values())
        $steps = $this->course->steps->values();
        $currentIndex = $steps->search(fn ($s) => $s->id === $this->currentStep->id);

        if ($currentIndex !== false && $currentIndex < $steps->count() - 1) {
            $this->currentStep = $steps->get($currentIndex + 1);
        } else {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'You\'ve reached the end of this course preview!',
            ]);
        }
    }

    public function startEnrollment(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // For staff users without a participant account, enable preview mode
        if (! $user->participant) {
            $this->previewMode = true;
            $this->currentStep = $this->course->steps->first();
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Previewing course as staff member.',
            ]);

            return;
        }

        $this->enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $this->course->id,
            'mini_course_version_id' => $this->course->current_version_id,
            'participant_id' => $user->participant->id,
            'enrolled_by' => $user->id,
            'enrollment_source' => MiniCourseEnrollment::SOURCE_SELF_ENROLLED,
            'status' => MiniCourseEnrollment::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'current_step_id' => $this->course->steps->first()?->id,
        ]);

        $this->currentStep = $this->course->steps->first();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'You have been enrolled in this course!',
        ]);
    }

    public function getNextStepProperty(): ?MiniCourseStep
    {
        if (! $this->currentStep) {
            return null;
        }
        $steps = $this->course->steps->values();
        $currentIndex = $steps->search(fn ($s) => $s->id === $this->currentStep->id);

        if ($currentIndex !== false && $currentIndex < $steps->count() - 1) {
            return $steps->get($currentIndex + 1);
        }

        return null;
    }

    public function getPreviousStepProperty(): ?MiniCourseStep
    {
        if (! $this->currentStep) {
            return null;
        }
        $steps = $this->course->steps->values();
        $currentIndex = $steps->search(fn ($s) => $s->id === $this->currentStep->id);

        if ($currentIndex !== false && $currentIndex > 0) {
            return $steps->get($currentIndex - 1);
        }

        return null;
    }

    public function getStepProgressProperty(): array
    {
        if (! $this->enrollment) {
            return [];
        }

        return MiniCourseStepProgress::where('enrollment_id', $this->enrollment->id)
            ->pluck('status', 'step_id')
            ->toArray();
    }

    /**
     * Check if the current user can push content to downstream organizations.
     */
    public function getCanPushProperty(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        return ($user->isAdmin() && $hasDownstream) || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Open the push modal for this course.
     */
    public function openPushModal(): void
    {
        $this->dispatch('openPushResource', $this->course->id);
    }

    public function render()
    {
        return view('livewire.mini-course-viewer', [
            'stepProgress' => $this->stepProgress,
            'canPush' => $this->canPush,
        ])->layout('layouts.dashboard', ['title' => 'Course Viewer']);
    }
}
