<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Models\CohortMember;
use App\Services\TerminologyService;
use Livewire\Component;
use Livewire\WithPagination;

class CohortDetail extends Component
{
    use WithPagination;

    public Cohort $cohort;
    public string $memberSearch = '';
    public string $memberStatusFilter = '';
    public string $memberRoleFilter = '';

    protected TerminologyService $terminology;

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function mount(Cohort $cohort): void
    {
        $this->cohort = $cohort->load(['course', 'semester', 'creator']);
    }

    public function updateStatus(string $status): void
    {
        $this->cohort->update(['status' => $status]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->terminology->get('cohort_singular') . ' status updated.',
        ]);
    }

    public function removeMember(int $memberId): void
    {
        $member = CohortMember::findOrFail($memberId);
        $member->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->terminology->get('learner_singular') . ' removed from ' . $this->terminology->get('cohort_singular') . '.',
        ]);
    }

    public function render()
    {
        $membersQuery = $this->cohort->members()
            ->with('user')
            ->when($this->memberSearch, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('first_name', 'like', "%{$this->memberSearch}%")
                       ->orWhere('last_name', 'like', "%{$this->memberSearch}%")
                       ->orWhere('email', 'like', "%{$this->memberSearch}%");
                });
            })
            ->when($this->memberStatusFilter, fn($q) => $q->where('status', $this->memberStatusFilter))
            ->when($this->memberRoleFilter, fn($q) => $q->where('role', $this->memberRoleFilter))
            ->orderBy('enrolled_at', 'desc');

        $members = $membersQuery->paginate(20);

        // Stats
        $stats = [
            'total_members' => $this->cohort->members()->count(),
            'active_members' => $this->cohort->members()->whereIn('status', ['enrolled', 'active'])->count(),
            'completed' => $this->cohort->members()->where('status', 'completed')->count(),
            'avg_progress' => round($this->cohort->members()->avg('progress_percent') ?? 0),
        ];

        return view('livewire.cohorts.cohort-detail', [
            'members' => $members,
            'stats' => $stats,
            'statusOptions' => Cohort::getStatusOptions(),
            'memberStatusOptions' => CohortMember::getStatusOptions(),
            'memberRoleOptions' => CohortMember::getRoleOptions(),
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
