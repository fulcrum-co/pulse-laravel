<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Models\MiniCourse;
use App\Models\Semester;
use App\Services\TerminologyService;
use Livewire\Component;
use Livewire\WithPagination;

class CohortManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $courseFilter = '';
    public string $semesterFilter = '';
    public string $visibilityFilter = '';
    public string $sortField = 'start_date';
    public string $sortDirection = 'desc';

    // Create/Edit modal
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingCohortId = null;

    // Form fields
    public ?int $mini_course_id = null;
    public ?int $semester_id = null;
    public string $name = '';
    public string $description = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $visibility_status = 'private';
    public string $status = 'draft';
    public ?int $max_capacity = null;
    public bool $allow_self_enrollment = false;
    public bool $drip_content = false;

    protected TerminologyService $terminology;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'courseFilter' => ['except' => ''],
        'semesterFilter' => ['except' => ''],
    ];

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function rules(): array
    {
        return [
            'mini_course_id' => 'required|exists:mini_courses,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'visibility_status' => 'required|in:public,gated,private',
            'status' => 'required|in:draft,enrollment_open,active,completed,archived',
            'max_capacity' => 'nullable|integer|min:1',
            'allow_self_enrollment' => 'boolean',
            'drip_content' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $cohortId): void
    {
        $cohort = Cohort::findOrFail($cohortId);

        $this->editingCohortId = $cohort->id;
        $this->mini_course_id = $cohort->mini_course_id;
        $this->semester_id = $cohort->semester_id;
        $this->name = $cohort->name;
        $this->description = $cohort->description ?? '';
        $this->start_date = $cohort->start_date->format('Y-m-d');
        $this->end_date = $cohort->end_date->format('Y-m-d');
        $this->visibility_status = $cohort->visibility_status;
        $this->status = $cohort->status;
        $this->max_capacity = $cohort->max_capacity;
        $this->allow_self_enrollment = $cohort->allow_self_enrollment;
        $this->drip_content = $cohort->drip_content;

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingCohortId = null;
        $this->mini_course_id = null;
        $this->semester_id = null;
        $this->name = '';
        $this->description = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->visibility_status = 'private';
        $this->status = 'draft';
        $this->max_capacity = null;
        $this->allow_self_enrollment = false;
        $this->drip_content = false;
        $this->isEditing = false;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        $data = [
            'org_id' => $user->org_id,
            'mini_course_id' => $this->mini_course_id,
            'semester_id' => $this->semester_id ?: null,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'visibility_status' => $this->visibility_status,
            'status' => $this->status,
            'max_capacity' => $this->max_capacity ?: null,
            'allow_self_enrollment' => $this->allow_self_enrollment,
            'drip_content' => $this->drip_content,
        ];

        if ($this->isEditing && $this->editingCohortId) {
            $cohort = Cohort::findOrFail($this->editingCohortId);
            $cohort->update($data);
            $message = $this->terminology->get('cohort_singular') . ' updated successfully.';
        } else {
            $data['created_by'] = $user->id;
            Cohort::create($data);
            $message = $this->terminology->get('cohort_singular') . ' created successfully.';
        }

        $this->closeModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function delete(int $cohortId): void
    {
        $cohort = Cohort::findOrFail($cohortId);

        if ($cohort->members()->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete ' . $this->terminology->get('cohort_singular') . ' with enrolled members.',
            ]);
            return;
        }

        $cohort->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->terminology->get('cohort_singular') . ' deleted successfully.',
        ]);
    }

    public function duplicate(int $cohortId): void
    {
        $cohort = Cohort::findOrFail($cohortId);
        $user = auth()->user();

        $newCohort = $cohort->replicate(['id', 'created_at', 'updated_at']);
        $newCohort->name = $cohort->name . ' (Copy)';
        $newCohort->status = Cohort::STATUS_DRAFT;
        $newCohort->created_by = $user->id;
        $newCohort->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->terminology->get('cohort_singular') . ' duplicated successfully.',
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        $query = Cohort::query()
            ->where('org_id', $user->org_id)
            ->with(['course', 'semester', 'creator'])
            ->withCount('members');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhereHas('course', function ($q2) {
                      $q2->where('title', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->courseFilter) {
            $query->where('mini_course_id', $this->courseFilter);
        }

        if ($this->semesterFilter) {
            $query->where('semester_id', $this->semesterFilter);
        }

        if ($this->visibilityFilter) {
            $query->where('visibility_status', $this->visibilityFilter);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $cohorts = $query->paginate(15);

        // Get filter options
        $courses = MiniCourse::where('org_id', $user->org_id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title']);

        $semesters = Semester::where('org_id', $user->org_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('livewire.cohorts.cohort-manager', [
            'cohorts' => $cohorts,
            'courses' => $courses,
            'semesters' => $semesters,
            'statusOptions' => Cohort::getStatusOptions(),
            'visibilityOptions' => Cohort::getVisibilityOptions(),
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
