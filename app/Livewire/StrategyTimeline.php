<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use Livewire\Component;
use Carbon\Carbon;

class StrategyTimeline extends Component
{
    public StrategicPlan $strategy;

    // Expanded states
    public $expandedFocusAreas = [];
    public $expandedObjectives = [];

    public function mount(StrategicPlan $strategy)
    {
        $this->strategy = $strategy;

        // Expand all by default
        foreach ($strategy->focusAreas as $fa) {
            $this->expandedFocusAreas[$fa->id] = true;
            foreach ($fa->objectives as $obj) {
                $this->expandedObjectives[$obj->id] = true;
            }
        }
    }

    public function toggleFocusArea($id)
    {
        $this->expandedFocusAreas[$id] = !($this->expandedFocusAreas[$id] ?? false);
    }

    public function toggleObjective($id)
    {
        $this->expandedObjectives[$id] = !($this->expandedObjectives[$id] ?? false);
    }

    /**
     * Get the timeline data for the Gantt chart.
     */
    public function getTimelineData(): array
    {
        $items = [];
        $startDate = $this->strategy->start_date;
        $endDate = $this->strategy->end_date;

        foreach ($this->strategy->focusAreas as $fa) {
            $items[] = [
                'id' => 'fa_' . $fa->id,
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
                        'id' => 'obj_' . $obj->id,
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
                                'id' => 'act_' . $act->id,
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
        $current = $this->strategy->start_date->copy()->startOfMonth();
        $end = $this->strategy->end_date->copy()->endOfMonth();

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
        $timelineStart = $this->strategy->start_date->timestamp;
        $timelineEnd = $this->strategy->end_date->timestamp;
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
            'left' => $left . '%',
            'width' => $width . '%',
        ];
    }

    public function render()
    {
        $this->strategy->load(['focusAreas.objectives.activities']);

        return view('livewire.strategy-timeline', [
            'items' => $this->getTimelineData(),
            'months' => $this->getMonths(),
        ]);
    }
}
