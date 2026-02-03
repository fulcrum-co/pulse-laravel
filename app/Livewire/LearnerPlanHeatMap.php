<?php

namespace App\Livewire;

use App\Models\Learner;
use App\Services\ContactMetricService;
use Livewire\Component;

class LearnerPlanHeatMap extends Component
{
    public Learner $learner;

    public string $organizationYear;

    protected ContactMetricService $metricService;

    public function boot(ContactMetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    public function mount(Learner $learner, ?string $organizationYear = null)
    {
        $this->learner = $learner;
        $this->organizationYear = $organizationYear ?? $this->metricService->getCurrentOrganizationYear();
    }

    public function previousYear()
    {
        $parts = explode('-', $this->organizationYear);
        if (count($parts) === 2) {
            $startYear = (int) $parts[0] - 1;
            $endYear = (int) $parts[1] - 1;
            $this->organizationYear = $startYear.'-'.$endYear;
        }
    }

    public function nextYear()
    {
        $parts = explode('-', $this->organizationYear);
        if (count($parts) === 2) {
            $startYear = (int) $parts[0] + 1;
            $endYear = (int) $parts[1] + 1;
            $this->organizationYear = $startYear.'-'.$endYear;
        }
    }

    public function getHeatMapDataProperty()
    {
        return $this->metricService->getHeatMapData(
            $this->learner,
            $this->organizationYear,
            ['academics', 'attendance', 'behavior', 'life_skills']
        );
    }

    public function getCategoriesProperty()
    {
        return [
            'academics' => 'Academics',
            'attendance' => 'Attendance',
            'behavior' => 'Behavior',
            'life_skills' => 'Life Skills',
        ];
    }

    public function getQuartersProperty()
    {
        return [
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
        ];
    }

    public function render()
    {
        return view('livewire.learner-plan-heat-map', [
            'heatMapData' => $this->heatMapData,
            'categories' => $this->categories,
            'quarters' => $this->quarters,
        ]);
    }
}
