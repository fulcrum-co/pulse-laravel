<?php

namespace App\Livewire;

use App\Models\Student;
use App\Services\ContactMetricService;
use Livewire\Component;

class StudentPlanHeatMap extends Component
{
    public Student $student;

    public string $schoolYear;

    protected ContactMetricService $metricService;

    public function boot(ContactMetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    public function mount(Student $student, ?string $schoolYear = null)
    {
        $this->student = $student;
        $this->schoolYear = $schoolYear ?? $this->metricService->getCurrentSchoolYear();
    }

    public function previousYear()
    {
        $parts = explode('-', $this->schoolYear);
        if (count($parts) === 2) {
            $startYear = (int) $parts[0] - 1;
            $endYear = (int) $parts[1] - 1;
            $this->schoolYear = $startYear.'-'.$endYear;
        }
    }

    public function nextYear()
    {
        $parts = explode('-', $this->schoolYear);
        if (count($parts) === 2) {
            $startYear = (int) $parts[0] + 1;
            $endYear = (int) $parts[1] + 1;
            $this->schoolYear = $startYear.'-'.$endYear;
        }
    }

    public function getHeatMapDataProperty()
    {
        return $this->metricService->getHeatMapData(
            $this->student,
            $this->schoolYear,
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
        return view('livewire.student-plan-heat-map', [
            'heatMapData' => $this->heatMapData,
            'categories' => $this->categories,
            'quarters' => $this->quarters,
        ]);
    }
}
