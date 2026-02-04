<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use Carbon\Carbon;
use Livewire\Component;

class GoalTimeline extends Component
{
    public StrategicPlan $plan;

    // Expanded states
    public array $expandedGoals = [];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;

        // Expand all by default
        foreach ($plan->goals as $goal) {
            $this->expandedGoals[$goal->id] = true;
        }
    }

    public function toggleGoal(int $id)
    {
        $this->expandedGoals[$id] = ! ($this->expandedGoals[$id] ?? false);
    }

    /**
     * Get the timeline data for the Gantt chart.
     */
    public function getTimelineData(): array
    {
        $items = [];
        $startDate = $this->plan->start_date;
        $endDate = $this->plan->end_date;

        foreach ($this->plan->goals as $goal) {
            $goalStart = $goal->start_date ?? $startDate;
            $goalEnd = $goal->due_date ?? $endDate;

            // Determine goal status for timeline coloring
            $goalStatus = match ($goal->status) {
                'completed' => 'on_track',
                'in_progress' => 'on_track',
                'at_risk' => 'at_risk',
                default => 'not_started',
            };

            $items[] = [
                'id' => 'goal_'.$goal->id,
                'type' => 'goal', // Focus Area
                'title' => $goal->title,
                'start_date' => $goalStart->format('Y-m-d'),
                'end_date' => $goalEnd->format('Y-m-d'),
                'status' => $goalStatus,
                'progress' => $goal->calculateProgress(),
                'level' => 0,
            ];

            if ($this->expandedGoals[$goal->id] ?? true) {
                foreach ($goal->keyResults as $kr) {
                    $krStart = $kr->start_date ?? $goalStart;
                    $krEnd = $kr->due_date ?? $goalEnd;

                    // Determine KR status for timeline coloring
                    $krStatus = match ($kr->status) {
                        'completed', 'on_track' => 'on_track',
                        'in_progress' => 'on_track',
                        'at_risk' => 'at_risk',
                        'off_track' => 'off_track',
                        default => 'not_started',
                    };

                    $items[] = [
                        'id' => 'kr_'.$kr->id,
                        'type' => 'key_result', // Key Activity
                        'title' => $kr->title,
                        'start_date' => $krStart->format('Y-m-d'),
                        'end_date' => $krEnd->format('Y-m-d'),
                        'status' => $krStatus,
                        'progress' => $kr->calculateProgress(),
                        'level' => 1,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Get the months for the timeline header.
     */
    public function getMonths(): array
    {
        $months = [];
        $current = $this->plan->start_date->copy()->startOfMonth();
        $end = $this->plan->end_date->copy()->endOfMonth();

        while ($current <= $end) {
            $months[] = [
                'label' => $current->format('F Y'),
                'short' => $current->format('M Y'),
                'start' => $current->format('Y-m-d'),
                'days' => $current->daysInMonth,
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Calculate bar position and width as percentages.
     */
    public function getBarStyle(string $startDate, string $endDate): array
    {
        $timelineStart = $this->plan->start_date->timestamp;
        $timelineEnd = $this->plan->end_date->timestamp;
        $totalDuration = $timelineEnd - $timelineStart;

        if ($totalDuration <= 0) {
            return ['left' => '0%', 'width' => '100%'];
        }

        $barStart = Carbon::parse($startDate)->timestamp;
        $barEnd = Carbon::parse($endDate)->timestamp;

        $left = (($barStart - $timelineStart) / $totalDuration) * 100;
        $width = (($barEnd - $barStart) / $totalDuration) * 100;

        // Clamp values
        $left = max(0, min(100, $left));
        $width = max(1, min(100 - $left, $width));

        return [
            'left' => $left.'%',
            'width' => $width.'%',
        ];
    }

    public function render()
    {
        $this->plan->load(['goals.keyResults']);

        return view('livewire.goal-timeline', [
            'items' => $this->getTimelineData(),
            'months' => $this->getMonths(),
        ]);
    }
}
