<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Services\TerminologyService;
use Livewire\Component;

class DripScheduleBuilder extends Component
{
    public Cohort $cohort;
    public bool $dripEnabled = false;
    public array $schedule = [];

    protected TerminologyService $terminology;

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function mount(Cohort $cohort): void
    {
        $this->cohort = $cohort->load('course.steps');
        $this->dripEnabled = (bool) $cohort->drip_content;
        $this->loadSchedule();
    }

    protected function loadSchedule(): void
    {
        $existingSchedule = collect($this->cohort->drip_schedule ?? [])
            ->keyBy('step_id')
            ->toArray();

        $this->schedule = [];

        if ($this->cohort->course && $this->cohort->course->steps) {
            foreach ($this->cohort->course->steps as $index => $step) {
                $existing = $existingSchedule[$step->id] ?? null;

                $this->schedule[$step->id] = [
                    'step_id' => $step->id,
                    'title' => $step->title,
                    'order' => $index,
                    'days_after_start' => $existing['days_after_start'] ?? ($index * 7), // Default: weekly
                    'require_previous' => $existing['require_previous'] ?? true,
                    'notify_learners' => $existing['notify_learners'] ?? true,
                ];
            }
        }
    }

    public function toggleDrip(): void
    {
        $this->dripEnabled = !$this->dripEnabled;
        $this->cohort->update(['drip_content' => $this->dripEnabled]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Drip content ' . ($this->dripEnabled ? 'enabled' : 'disabled') . '.',
        ]);
    }

    public function updateDays(int $stepId, int $days): void
    {
        if (isset($this->schedule[$stepId])) {
            $this->schedule[$stepId]['days_after_start'] = max(0, $days);
        }
    }

    public function toggleRequirePrevious(int $stepId): void
    {
        if (isset($this->schedule[$stepId])) {
            $this->schedule[$stepId]['require_previous'] = !$this->schedule[$stepId]['require_previous'];
        }
    }

    public function toggleNotify(int $stepId): void
    {
        if (isset($this->schedule[$stepId])) {
            $this->schedule[$stepId]['notify_learners'] = !$this->schedule[$stepId]['notify_learners'];
        }
    }

    public function applyWeeklySchedule(): void
    {
        $day = 0;
        foreach ($this->schedule as $stepId => $item) {
            $this->schedule[$stepId]['days_after_start'] = $day;
            $day += 7;
        }
    }

    public function applyDailySchedule(): void
    {
        $day = 0;
        foreach ($this->schedule as $stepId => $item) {
            $this->schedule[$stepId]['days_after_start'] = $day;
            $day += 1;
        }
    }

    public function applyBiweeklySchedule(): void
    {
        $day = 0;
        foreach ($this->schedule as $stepId => $item) {
            $this->schedule[$stepId]['days_after_start'] = $day;
            $day += 14;
        }
    }

    public function releaseAllNow(): void
    {
        foreach ($this->schedule as $stepId => $item) {
            $this->schedule[$stepId]['days_after_start'] = 0;
        }
    }

    public function saveSchedule(): void
    {
        $scheduleData = [];
        foreach ($this->schedule as $stepId => $item) {
            $scheduleData[] = [
                'step_id' => $item['step_id'],
                'days_after_start' => $item['days_after_start'],
                'require_previous' => $item['require_previous'],
                'notify_learners' => $item['notify_learners'],
            ];
        }

        $this->cohort->update([
            'drip_schedule' => $scheduleData,
            'drip_content' => $this->dripEnabled,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Drip schedule saved successfully.',
        ]);
    }

    public function getReleaseDate(int $daysAfterStart): string
    {
        return $this->cohort->start_date->copy()
            ->addDays($daysAfterStart)
            ->format('M d, Y');
    }

    public function isReleased(int $daysAfterStart): bool
    {
        $releaseDate = $this->cohort->start_date->copy()->addDays($daysAfterStart);
        return $releaseDate <= now();
    }

    public function render()
    {
        // Sort schedule by days_after_start for timeline view
        $sortedSchedule = collect($this->schedule)
            ->sortBy('days_after_start')
            ->values()
            ->toArray();

        // Calculate cohort duration
        $cohortDays = $this->cohort->start_date->diffInDays($this->cohort->end_date);
        $maxScheduleDay = collect($this->schedule)->max('days_after_start');

        return view('livewire.cohorts.drip-schedule-builder', [
            'sortedSchedule' => $sortedSchedule,
            'cohortDays' => $cohortDays,
            'maxScheduleDay' => $maxScheduleDay,
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
