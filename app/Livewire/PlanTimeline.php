<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use Carbon\Carbon;
use Livewire\Component;

class PlanTimeline extends Component
{
    public StrategicPlan $plan;

    // Expanded states
    public $expandedFocusAreas = [];

    public $expandedObjectives = [];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;

        // Expand all by default
        foreach ($plan->focusAreas as $fa) {
            $this->expandedFocusAreas[$fa->id] = true;
            foreach ($fa->objectives as $obj) {
                $this->expandedObjectives[$obj->id] = true;
            }
        }
    }

    public function toggleFocusArea($id)
    {
        $this->expandedFocusAreas[$id] = ! ($this->expandedFocusAreas[$id] ?? false);
    }

    public function toggleObjective($id)
    {
        $this->expandedObjectives[$id] = ! ($this->expandedObjectives[$id] ?? false);
    }

    /**
     * Get the timeline data for the Gantt chart.
     */
    public function getTimelineData(): array
    {
        $items = [];
        $startDate = $this->plan->start_date;
        $endDate = $this->plan->end_date;

        foreach ($this->plan->focusAreas as $fa) {
            $items[] = [
                'id' => 'fa_'.$fa->id,
                'type' => 'focus_area',
                'title' => $fa->title,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $fa->status,
                'level' => 0,
            ];

            if ($this->expandedFocusAreas[$fa->id] ?? true) {
                foreach ($fa->objectives as $obj) {
                    $objStart = $obj->start_date ?? $startDate;
                    $objEnd = $obj->end_date ?? $endDate;

                    $items[] = [
                        'id' => 'obj_'.$obj->id,
                        'type' => 'objective',
                        'title' => $obj->title,
                        'start_date' => $objStart->format('Y-m-d'),
                        'end_date' => $objEnd->format('Y-m-d'),
                        'status' => $obj->status,
                        'level' => 1,
                    ];

                    if ($this->expandedObjectives[$obj->id] ?? true) {
                        foreach ($obj->activities as $act) {
                            $actStart = $act->start_date ?? $objStart;
                            $actEnd = $act->end_date ?? $objEnd;

                            $items[] = [
                                'id' => 'act_'.$act->id,
                                'type' => 'activity',
                                'title' => $act->title,
                                'start_date' => $actStart->format('Y-m-d'),
                                'end_date' => $actEnd->format('Y-m-d'),
                                'status' => $act->status,
                                'level' => 2,
                            ];
                        }
                    }
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
        $this->plan->load(['focusAreas.objectives.activities']);

        return view('livewire.plan-timeline', [
            'items' => $this->getTimelineData(),
            'months' => $this->getMonths(),
        ]);
    }
}
