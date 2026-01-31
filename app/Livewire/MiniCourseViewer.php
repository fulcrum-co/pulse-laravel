<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseStepProgress;
use App\Models\Student;
use Livewire\Component;

class MiniCourseViewer extends Component
{
    public MiniCourse $course;
    public ?MiniCourseEnrollment $enrollment = null;
    public ?MiniCourseStep $currentStep = null;
    public bool $showRationale = false;

    // For staff viewing student progress
    public ?int $viewingStudentId = null;

    public function mount(MiniCourse $course, ?int $studentId = null): void
    {
        $this->course = $course->load(['steps' => fn ($q) => $q->orderBy('sort_order'), 'creator']);
        $this->viewingStudentId = $studentId;

        // Check if current user has an enrollment
        $user = auth()->user();
        if ($user && $user->student) {
            $this->enrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
                ->where('student_id', $user->student->id)
                ->first();

            if ($this->enrollment) {
                $this->currentStep = $this->enrollment->currentStep ?? $this->course->steps->first();
            }
        }

        // If viewing as staff for a specific student
        if ($studentId) {
            $this->enrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
                ->where('student_id', $studentId)
                ->first();
        }

        // Default to first step if no enrollment
        if (!$this->currentStep && $this->course->steps->isNotEmpty()) {
            $this->currentStep = $this->course->steps->first();
        }
    }

    public function selectStep(int $stepId): void
    {
        $this->currentStep = $this->course->steps->find($stepId);
    }

    public function toggleRationale(): void
    {
        $this->showRationale = !$this->showRationale;
    }

    public function completeCurrentStep(): void
    {
        if (!$this->enrollment || !$this->currentStep) {
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

        // Move to next step
        $nextStep = $this->currentStep->next_step;
        if ($nextStep) {
            $this->currentStep = $nextStep;
            $this->enrollment->update(['current_step_id' => $nextStep->id]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Step completed!',
        ]);
    }

    public function startEnrollment(): void
    {
        $user = auth()->user();
        if (!$user || !$user->student) {
            return;
        }

        $this->enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $this->course->id,
            'mini_course_version_id' => $this->course->current_version_id,
            'student_id' => $user->student->id,
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

    public function getStepProgressProperty(): array
    {
        if (!$this->enrollment) {
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
        return $user->isAdmin() && $user->organization?->getDownstreamOrganizations()->count() > 0;
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
