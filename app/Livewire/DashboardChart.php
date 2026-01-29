<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SurveyAttempt;

class DashboardChart extends Component
{
    public function getChartDataProperty()
    {
        $user = auth()->user();
        $orgId = $user->org_id;

        // Get last 7 days of data
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates->push([
                'date' => $date->format('M d'),
                'thisWeek' => SurveyAttempt::whereHas('survey', function ($q) use ($orgId) {
                    $q->where('org_id', $orgId);
                })->whereDate('completed_at', $date)->count(),
                'lastWeek' => SurveyAttempt::whereHas('survey', function ($q) use ($orgId) {
                    $q->where('org_id', $orgId);
                })->whereDate('completed_at', $date->copy()->subWeek())->count(),
            ]);
        }

        return $dates;
    }

    public function render()
    {
        return view('livewire.dashboard-chart', [
            'chartData' => $this->chartData,
        ]);
    }
}
