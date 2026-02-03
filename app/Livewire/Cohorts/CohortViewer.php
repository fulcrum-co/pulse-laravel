<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Models\CohortMember;
use App\Models\CohortProgress;
use App\Models\MiniCourseStep;
use App\Services\TerminologyService;
use Livewire\Component;

class CohortViewer extends Component
{
    public Cohort $cohort;
    public ?CohortMember $membership = null;
    public ?MiniCourseStep $currentStep = null;
    public array $stepProgress = [];

    protected TerminologyService $terminology;

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function mount(Cohort $cohort): void
    {
        $this->cohort = $cohort->load(['course.steps', 'semester']);

        $user = auth()->user();
        $this->membership = CohortMember::where('cohort_id', $cohort->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$this->membership) {
            // Check if user can self-enroll
            if ($cohort->canEnroll()) {
                $this->enrollUser();
            } else {
                abort(403, 'You are not enrolled in this ' . $this->terminology->get('cohort_singular'));
            }
        }

        $this->loadProgress();
        $this->setCurrentStep();
    }

    protected function enrollUser(): void
    {
        $user = auth()->user();

        $this->membership = CohortMember::create([
            'cohort_id' => $this->cohort->id,
            'user_id' => $user->id,
            'role' => CohortMember::ROLE_STUDENT,
            'status' => CohortMember::STATUS_ENROLLED,
            'enrollment_source' => CohortMember::SOURCE_SELF_ENROLLED,
            'enrolled_at' => now(),
        ]);
    }

    protected function loadProgress(): void
    {
        if (!$this->membership) {
            return;
        }

        $progress = CohortProgress::where('cohort_member_id', $this->membership->id)
            ->get()
            ->keyBy('mini_course_step_id');

        $this->stepProgress = $progress->toArray();
    }

    protected function setCurrentStep(): void
    {
        if (!$this->membership) {
            return;
        }

        // Get current step from membership or find first incomplete step
        if ($this->membership->current_step_id) {
            $this->currentStep = MiniCourseStep::find($this->membership->current_step_id);
        } else {
            // Find first step not completed
            $completedStepIds = collect($this->stepProgress)
                ->filter(fn($p) => $p['status'] === 'completed')
                ->pluck('mini_course_step_id')
                ->toArray();

            $this->currentStep = $this->cohort->course->steps
                ->whereNotIn('id', $completedStepIds)
                ->first();

            if (!$this->currentStep) {
                $this->currentStep = $this->cohort->course->steps->first();
            }
        }
    }

    public function selectStep(int $stepId): void
    {
        $step = MiniCourseStep::findOrFail($stepId);

        // Check if step is available (drip content)
        if (!$this->isStepAvailable($step)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'This ' . $this->terminology->get('step_singular') . ' is not yet available.',
            ]);
            return;
        }

        $this->currentStep = $step;
        $this->membership->update(['current_step_id' => $stepId]);
    }

    public function startStep(): void
    {
        if (!$this->currentStep || !$this->membership) {
            return;
        }

        $progress = CohortProgress::firstOrCreate(
            [
                'cohort_member_id' => $this->membership->id,
                'mini_course_step_id' => $this->currentStep->id,
            ],
            ['status' => CohortProgress::STATUS_NOT_STARTED]
        );

        if ($progress->status === CohortProgress::STATUS_NOT_STARTED) {
            $progress->start();
            $this->loadProgress();

            // Update membership status if first step
            if ($this->membership->status === CohortMember::STATUS_ENROLLED) {
                $this->membership->start();
            }
        }
    }

    public function completeStep(array $responseData = []): void
    {
        if (!$this->currentStep || !$this->membership) {
            return;
        }

        $progress = CohortProgress::where('cohort_member_id', $this->membership->id)
            ->where('mini_course_step_id', $this->currentStep->id)
            ->first();

        if ($progress) {
            $progress->complete($responseData);
            $this->loadProgress();

            // Move to next step
            $this->advanceToNextStep();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $this->terminology->get('step_singular') . ' completed!',
            ]);
        }
    }

    public function addTimeSpent(int $seconds): void
    {
        if (!$this->currentStep || !$this->membership) {
            return;
        }

        $progress = CohortProgress::where('cohort_member_id', $this->membership->id)
            ->where('mini_course_step_id', $this->currentStep->id)
            ->first();

        if ($progress) {
            $progress->addTimeSpent($seconds);
        }
    }

    protected function advanceToNextStep(): void
    {
        $steps = $this->cohort->course->steps;
        $currentIndex = $steps->search(fn($s) => $s->id === $this->currentStep->id);

        if ($currentIndex !== false && $currentIndex < $steps->count() - 1) {
            $nextStep = $steps[$currentIndex + 1];
            if ($this->isStepAvailable($nextStep)) {
                $this->currentStep = $nextStep;
                $this->membership->update(['current_step_id' => $nextStep->id]);
            }
        }
    }

    public function isStepAvailable(MiniCourseStep $step): bool
    {
        // If no drip content, all steps are available
        if (!$this->cohort->drip_content || empty($this->cohort->drip_schedule)) {
            return true;
        }

        // Check drip schedule
        $schedule = collect($this->cohort->drip_schedule)
            ->firstWhere('step_id', $step->id);

        if (!$schedule) {
            return true; // No schedule for this step, available immediately
        }

        $releaseDate = $this->cohort->start_date->copy()
            ->addDays($schedule['days_after_start'] ?? 0);

        return $releaseDate <= now();
    }

    public function getStepStatus(int $stepId): string
    {
        $progress = $this->stepProgress[$stepId] ?? null;

        if (!$progress) {
            return 'not_started';
        }

        return $progress['status'];
    }

    public function render()
    {
        $steps = $this->cohort->course->steps ?? collect();

        return view('livewire.cohorts.cohort-viewer', [
            'steps' => $steps,
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
