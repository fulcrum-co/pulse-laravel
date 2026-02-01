<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class ContactList extends Component
{
    use WithPagination;

    public $search = '';

    public $riskFilter = '';

    public $gradeFilter = '';

    public $selectedIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'riskFilter' => ['except' => ''],
        'gradeFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRiskFilter()
    {
        $this->resetPage();
    }

    public function updatingGradeFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->riskFilter = '';
        $this->gradeFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        // Get all organization IDs this user can access (includes assigned orgs for consultants)
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $contacts = Student::with('user')
            ->whereIn('org_id', $accessibleOrgIds)
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->riskFilter, function ($query) {
                $query->where('risk_level', $this->riskFilter);
            })
            ->when($this->gradeFilter, function ($query) {
                $query->where('grade_level', $this->gradeFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.contact-list', [
            'contacts' => $contacts,
        ]);
    }
}
