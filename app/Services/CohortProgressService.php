<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\Cohort;
use App\Models\CohortMember;
use App\Models\CohortProgress;
use App\Models\LeadScore;
use App\Models\LeadScoreEvent;
use App\Models\MiniCourseStep;
use Illuminate\Support\Facades\DB;

class CohortProgressService
{
    public function __construct(
        protected ?CertificateService $certificateService = null
    ) {}

    /**
     * Start a step for a cohort member.
     */
    public function startStep(CohortMember $member, MiniCourseStep $step): CohortProgress
    {
        $progress = CohortProgress::firstOrCreate(
            [
                'cohort_member_id' => $member->id,
                'mini_course_step_id' => $step->id,
            ],
            ['status' => CohortProgress::STATUS_NOT_STARTED]
        );

        if ($progress->status === CohortProgress::STATUS_NOT_STARTED) {
            $progress->start();

            // Update member status if this is their first step
            if ($member->status === CohortMember::STATUS_ENROLLED) {
                $member->start();
            }
        }

        return $progress;
    }

    /**
     * Complete a step for a cohort member.
     */
    public function completeStep(
        CohortMember $member,
        MiniCourseStep $step,
        array $responseData = [],
        ?float $score = null
    ): CohortProgress {
        $progress = CohortProgress::where('cohort_member_id', $member->id)
            ->where('mini_course_step_id', $step->id)
            ->first();

        if (!$progress) {
            $progress = $this->startStep($member, $step);
        }

        $progress->complete($responseData, $score);

        // Recalculate overall progress
        $this->updateMemberProgress($member);

        // Check if course is complete
        if ($this->isCourseComplete($member)) {
            $this->handleCourseCompletion($member);
        }

        // Award lead score points
        $this->awardStepCompletionPoints($member, $step);

        return $progress;
    }

    /**
     * Add time spent on a step.
     */
    public function addTimeSpent(CohortMember $member, MiniCourseStep $step, int $seconds): void
    {
        $progress = CohortProgress::where('cohort_member_id', $member->id)
            ->where('mini_course_step_id', $step->id)
            ->first();

        if ($progress) {
            $progress->addTimeSpent($seconds);

            // Also update member's total time
            $member->increment('time_spent_seconds', $seconds);
        }
    }

    /**
     * Calculate and update member's overall progress percentage.
     */
    public function updateMemberProgress(CohortMember $member): int
    {
        $cohort = $member->cohort;
        $course = $cohort->course;

        if (!$course) {
            return 0;
        }

        $totalSteps = $course->steps()->count();
        if ($totalSteps === 0) {
            return 0;
        }

        $completedSteps = CohortProgress::where('cohort_member_id', $member->id)
            ->where('status', CohortProgress::STATUS_COMPLETED)
            ->count();

        $progressPercent = (int) round(($completedSteps / $totalSteps) * 100);

        $member->update([
            'progress_percent' => $progressPercent,
            'steps_completed' => $completedSteps,
        ]);

        return $progressPercent;
    }

    /**
     * Check if member has completed all steps.
     */
    public function isCourseComplete(CohortMember $member): bool
    {
        $cohort = $member->cohort;
        $course = $cohort->course;

        if (!$course) {
            return false;
        }

        $totalSteps = $course->steps()->count();
        if ($totalSteps === 0) {
            return true; // Empty course is "complete"
        }

        $completedSteps = CohortProgress::where('cohort_member_id', $member->id)
            ->where('status', CohortProgress::STATUS_COMPLETED)
            ->count();

        return $completedSteps >= $totalSteps;
    }

    /**
     * Handle course completion - update status, generate certificate, award points.
     */
    public function handleCourseCompletion(CohortMember $member): void
    {
        // Already completed?
        if ($member->status === CohortMember::STATUS_COMPLETED) {
            return;
        }

        DB::transaction(function () use ($member) {
            // Update member status
            $member->update([
                'status' => CohortMember::STATUS_COMPLETED,
                'completed_at' => now(),
                'progress_percent' => 100,
            ]);

            // Generate certificate if enabled
            $course = $member->cohort->course;
            if ($course?->certificate_enabled && $this->certificateService) {
                $this->certificateService->generate($member);
            }

            // Award completion points
            $this->awardCourseCompletionPoints($member);
        });
    }

    /**
     * Get the next available step for a member.
     */
    public function getNextStep(CohortMember $member): ?MiniCourseStep
    {
        $cohort = $member->cohort;
        $course = $cohort->course;

        if (!$course) {
            return null;
        }

        $completedStepIds = CohortProgress::where('cohort_member_id', $member->id)
            ->where('status', CohortProgress::STATUS_COMPLETED)
            ->pluck('mini_course_step_id')
            ->toArray();

        $steps = $course->steps()->orderBy('order')->get();

        foreach ($steps as $step) {
            if (!in_array($step->id, $completedStepIds)) {
                // Check if step is available (drip content)
                if ($this->isStepAvailable($member, $step)) {
                    return $step;
                }
            }
        }

        return null;
    }

    /**
     * Check if a step is available based on drip schedule.
     */
    public function isStepAvailable(CohortMember $member, MiniCourseStep $step): bool
    {
        $cohort = $member->cohort;

        // No drip content = all available
        if (!$cohort->drip_content || empty($cohort->drip_schedule)) {
            return true;
        }

        $schedule = collect($cohort->drip_schedule)
            ->firstWhere('step_id', $step->id);

        if (!$schedule) {
            return true; // No schedule for this step
        }

        // Check days after start
        $releaseDate = $cohort->start_date->copy()
            ->addDays($schedule['days_after_start'] ?? 0);

        if ($releaseDate > now()) {
            return false;
        }

        // Check if previous step required
        if ($schedule['require_previous'] ?? false) {
            $previousStep = $this->getPreviousStep($cohort, $step);
            if ($previousStep) {
                $previousProgress = CohortProgress::where('cohort_member_id', $member->id)
                    ->where('mini_course_step_id', $previousStep->id)
                    ->first();

                if (!$previousProgress || $previousProgress->status !== CohortProgress::STATUS_COMPLETED) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the step before the given step.
     */
    protected function getPreviousStep(Cohort $cohort, MiniCourseStep $step): ?MiniCourseStep
    {
        $course = $cohort->course;
        if (!$course) {
            return null;
        }

        $steps = $course->steps()->orderBy('order')->get();
        $currentIndex = $steps->search(fn($s) => $s->id === $step->id);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return $steps[$currentIndex - 1];
    }

    /**
     * Award lead score points for completing a step.
     */
    protected function awardStepCompletionPoints(CohortMember $member, MiniCourseStep $step): void
    {
        $user = $member->user;
        if (!$user?->org_id) {
            return;
        }

        $leadScore = LeadScore::firstOrCreate(
            [
                'org_id' => $user->org_id,
                'user_id' => $user->id,
            ],
            ['total_score' => 0]
        );

        // Award points for module/step completion
        $points = LeadScore::POINTS_MODULE_COMPLETED;

        $leadScore->addPoints(
            $points,
            LeadScoreEvent::TYPE_MODULE_COMPLETED,
            "Completed step: {$step->title}",
            $step
        );
    }

    /**
     * Award lead score points for completing a course.
     */
    protected function awardCourseCompletionPoints(CohortMember $member): void
    {
        $user = $member->user;
        if (!$user?->org_id) {
            return;
        }

        $leadScore = LeadScore::firstOrCreate(
            [
                'org_id' => $user->org_id,
                'user_id' => $user->id,
            ],
            ['total_score' => 0]
        );

        $course = $member->cohort->course;

        // Award points for course completion
        $points = LeadScore::POINTS_COURSE_COMPLETED;

        $leadScore->addPoints(
            $points,
            LeadScoreEvent::TYPE_COURSE_COMPLETED,
            "Completed course: {$course?->title}",
            $course
        );

        // Update courses completed count
        $leadScore->increment('courses_completed');
    }

    /**
     * Get progress summary for a member.
     */
    public function getProgressSummary(CohortMember $member): array
    {
        $cohort = $member->cohort;
        $course = $cohort->course;

        $totalSteps = $course?->steps()->count() ?? 0;
        $completedSteps = CohortProgress::where('cohort_member_id', $member->id)
            ->where('status', CohortProgress::STATUS_COMPLETED)
            ->count();

        $inProgressSteps = CohortProgress::where('cohort_member_id', $member->id)
            ->where('status', CohortProgress::STATUS_IN_PROGRESS)
            ->count();

        $totalTimeSpent = CohortProgress::where('cohort_member_id', $member->id)
            ->sum('time_spent_seconds');

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'in_progress_steps' => $inProgressSteps,
            'not_started_steps' => $totalSteps - $completedSteps - $inProgressSteps,
            'progress_percent' => $member->progress_percent,
            'total_time_spent' => $totalTimeSpent,
            'is_complete' => $member->status === CohortMember::STATUS_COMPLETED,
            'completed_at' => $member->completed_at,
        ];
    }
}
