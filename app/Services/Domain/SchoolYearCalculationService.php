<?php

declare(strict_types=1);

namespace App\Services\Domain;

use Carbon\Carbon;

class OrganizationYearCalculationService
{
    /**
     * Get current organization year string.
     */
    public function getCurrentOrganizationYear(): string
    {
        $now = now();
        $year = $now->month >= 8 ? $now->year : $now->year - 1;

        return $year . '-' . ($year + 1);
    }

    /**
     * Get current quarter.
     * Assumes organization year starts in August.
     * Q1: Aug-Oct, Q2: Nov-Jan, Q3: Feb-Apr, Q4: May-Jul
     */
    public function getCurrentQuarter(): int
    {
        $month = now()->month;

        return match (true) {
            $month >= 8 && $month <= 10 => 1,
            $month >= 11 || $month <= 1 => 2,
            $month >= 2 && $month <= 4 => 3,
            default => 4,
        };
    }

    /**
     * Get organization year for a specific date.
     */
    public function getOrganizationYearForDate(Carbon $date): string
    {
        $year = $date->month >= 8 ? $date->year : $date->year - 1;

        return $year . '-' . ($year + 1);
    }

    /**
     * Get quarter for a specific date.
     */
    public function getQuarterForDate(Carbon $date): int
    {
        $month = $date->month;

        return match (true) {
            $month >= 8 && $month <= 10 => 1,
            $month >= 11 || $month <= 1 => 2,
            $month >= 2 && $month <= 4 => 3,
            default => 4,
        };
    }
}
