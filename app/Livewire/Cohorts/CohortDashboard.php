<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Models\CohortMember;
use App\Services\TerminologyService;
use Livewire\Component;

class CohortDashboard extends Component
{
    protected TerminologyService $terminology;

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function render()
    {
        $user = auth()->user();

        // Get user's cohort memberships
        $memberships = CohortMember::where('user_id', $user->id)
            ->with(['cohort.course', 'cohort.semester', 'currentStep'])
            ->get();

        // Categorize memberships
        $activeCohorts = $memberships->filter(fn($m) =>
            in_array($m->status, ['enrolled', 'active']) &&
            in_array($m->cohort->status, ['enrollment_open', 'active'])
        );

        $completedCohorts = $memberships->filter(fn($m) => $m->status === 'completed');

        $upcomingCohorts = $memberships->filter(fn($m) =>
            $m->cohort->start_date > now() &&
            in_array($m->status, ['enrolled', 'active'])
        );

        // Stats
        $stats = [
            'total_enrolled' => $memberships->count(),
            'in_progress' => $activeCohorts->count(),
            'completed' => $completedCohorts->count(),
            'total_time_spent' => $memberships->sum(fn($m) => $m->total_time_spent ?? 0),
        ];

        // Available cohorts to join (public/gated with self-enrollment)
        $availableCohorts = Cohort::where('org_id', $user->org_id)
            ->where('allow_self_enrollment', true)
            ->whereIn('status', ['enrollment_open', 'active'])
            ->whereIn('visibility_status', ['public', 'gated'])
            ->whereNotIn('id', $memberships->pluck('cohort_id'))
            ->with('course')
            ->where(function ($q) {
                $q->whereNull('max_capacity')
                  ->orWhereRaw('(SELECT COUNT(*) FROM cohort_members WHERE cohort_id = cohorts.id) < max_capacity');
            })
            ->limit(6)
            ->get();

        return view('livewire.cohorts.cohort-dashboard', [
            'activeCohorts' => $activeCohorts,
            'completedCohorts' => $completedCohorts,
            'upcomingCohorts' => $upcomingCohorts,
            'availableCohorts' => $availableCohorts,
            'stats' => $stats,
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
