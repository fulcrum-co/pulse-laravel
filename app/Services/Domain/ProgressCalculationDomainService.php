<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * Domain service for progress calculation and course completion rules.
 * Handles all business logic for calculating progress percentages,
 * checking completion status, and managing drip content availability.
 */
class ProgressCalculationDomainService
{
    /**
     * Calculate overall progress percentage based on completed steps.
     */
    public function calculateProgressPercent(int $completedSteps, int $totalSteps): int
    {
        if ($totalSteps === 0) {
            return 0;
        }

        return (int) round(($completedSteps / $totalSteps) * 100);
    }

    /**
     * Determine if a course is complete based on step counts.
     */
    public function isCourseComplete(int $completedSteps, int $totalSteps): bool
    {
        if ($totalSteps === 0) {
            return true; // Empty course is "complete"
        }

        return $completedSteps >= $totalSteps;
    }

    /**
     * Calculate the release date for a drip content step.
     */
    public function calculateDripReleaseDate(\DateTime $cohortStartDate, int $daysAfterStart): \DateTime
    {
        return $cohortStartDate->copy()->addDays($daysAfterStart);
    }

    /**
     * Check if a drip content step is available based on release date.
     */
    public function isDripContentAvailable(\DateTime $releaseDate): bool
    {
        return $releaseDate <= now();
    }

    /**
     * Check if previous step is required and completed.
     */
    public function isPreviousStepCompleted(?bool $requirePrevious, ?bool $previousCompleted): bool
    {
        if (!$requirePrevious) {
            return true;
        }

        return $previousCompleted ?? false;
    }

    /**
     * Determine if a step is available based on all drip constraints.
     */
    public function isStepAvailable(
        \DateTime $releaseDate,
        bool $requirePrevious = false,
        bool $previousCompleted = false
    ): bool
    {
        // Check release date
        if (!$this->isDripContentAvailable($releaseDate)) {
            return false;
        }

        // Check prerequisite
        if (!$this->isPreviousStepCompleted($requirePrevious, $previousCompleted)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the number of not-started steps.
     */
    public function calculateNotStartedCount(
        int $totalSteps,
        int $completedSteps,
        int $inProgressSteps
    ): int
    {
        return $totalSteps - $completedSteps - $inProgressSteps;
    }
}
