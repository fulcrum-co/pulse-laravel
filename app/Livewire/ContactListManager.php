<?php

namespace App\Livewire;

use App\Models\Classroom;
use App\Models\ContactList;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ContactListManager extends Component
{
    use WithPagination;

    // Search and filters
    public string $search = '';

    public string $filterType = '';

    // Create/Edit modal
    public bool $showModal = false;

    public ?ContactList $editingList = null;

    // Form fields
    public string $listName = '';

    public string $listDescription = '';

    public string $listType = 'student';

    public bool $isDynamic = false;

    public array $filterCriteria = [];

    // Member management modal
    public bool $showMembersModal = false;

    public ?ContactList $viewingList = null;

    public string $memberSearch = '';

    public array $selectedMembers = [];

    // Preview for dynamic lists
    public int $previewCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    // ============================================
    // LIST CRUD
    // ============================================

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $listId): void
    {
        $this->editingList = ContactList::find($listId);

        if ($this->editingList) {
            $this->listName = $this->editingList->name;
            $this->listDescription = $this->editingList->description ?? '';
            $this->listType = $this->editingList->list_type;
            $this->isDynamic = $this->editingList->is_dynamic;
            $this->filterCriteria = $this->editingList->filter_criteria ?? [];
            $this->showModal = true;
            $this->updatePreview();
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingList = null;
        $this->listName = '';
        $this->listDescription = '';
        $this->listType = 'student';
        $this->isDynamic = false;
        $this->filterCriteria = [];
        $this->previewCount = 0;
    }

    public function saveList(): void
    {
        $this->validate([
            'listName' => 'required|string|max:255',
            'listType' => 'required|in:student,teacher,mixed',
        ]);

        $user = auth()->user();

        $data = [
            'name' => $this->listName,
            'description' => $this->listDescription ?: null,
            'list_type' => $this->listType,
            'is_dynamic' => $this->isDynamic,
            'filter_criteria' => $this->isDynamic ? $this->filterCriteria : null,
        ];

        if ($this->editingList) {
            $this->editingList->update($data);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Contact list updated successfully.',
            ]);
        } else {
            ContactList::create([
                ...$data,
                'org_id' => $user->org_id,
                'created_by' => $user->id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Contact list created successfully.',
            ]);
        }

        $this->closeModal();
    }

    public function deleteList(int $listId): void
    {
        $list = ContactList::find($listId);

        if ($list && auth()->user()->canAccessOrganization($list->org_id)) {
            $list->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Contact list deleted.',
            ]);
        }
    }

    // ============================================
    // FILTER CRITERIA MANAGEMENT
    // ============================================

    public function setFilterValue(string $key, $value): void
    {
        if (empty($value) || (is_array($value) && empty($value))) {
            unset($this->filterCriteria[$key]);
        } else {
            $this->filterCriteria[$key] = $value;
        }

        $this->updatePreview();
    }

    public function toggleFilterArrayValue(string $key, $value): void
    {
        if (! isset($this->filterCriteria[$key])) {
            $this->filterCriteria[$key] = [];
        }

        if (in_array($value, $this->filterCriteria[$key])) {
            $this->filterCriteria[$key] = array_values(
                array_filter($this->filterCriteria[$key], fn ($v) => $v !== $value)
            );

            if (empty($this->filterCriteria[$key])) {
                unset($this->filterCriteria[$key]);
            }
        } else {
            $this->filterCriteria[$key][] = $value;
        }

        $this->updatePreview();
    }

    public function updatePreview(): void
    {
        if (! $this->isDynamic || empty($this->filterCriteria)) {
            $this->previewCount = 0;

            return;
        }

        // Create temporary list to get count
        $tempList = new ContactList([
            'org_id' => auth()->user()->org_id,
            'list_type' => $this->listType,
            'filter_criteria' => $this->filterCriteria,
            'is_dynamic' => true,
        ]);

        $this->previewCount = $tempList->getContactsQuery()->count();
    }

    // ============================================
    // MEMBER MANAGEMENT
    // ============================================

    public function openMembersModal(int $listId): void
    {
        $this->viewingList = ContactList::with(['students.user', 'users'])->find($listId);

        if ($this->viewingList) {
            $this->showMembersModal = true;
            $this->memberSearch = '';
            $this->selectedMembers = [];
        }
    }

    public function closeMembersModal(): void
    {
        $this->showMembersModal = false;
        $this->viewingList = null;
        $this->memberSearch = '';
        $this->selectedMembers = [];
    }

    public function addSelectedMembers(): void
    {
        if (! $this->viewingList || empty($this->selectedMembers)) {
            return;
        }

        $user = auth()->user();
        $studentIds = [];
        $userIds = [];

        foreach ($this->selectedMembers as $member) {
            [$type, $id] = explode(':', $member);
            if ($type === 'student') {
                $studentIds[] = (int) $id;
            } else {
                $userIds[] = (int) $id;
            }
        }

        $this->viewingList->addContacts($studentIds, $userIds, $user->id);
        $this->viewingList->refresh();
        $this->selectedMembers = [];

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Members added to list.',
        ]);
    }

    public function removeMember(string $type, int $id): void
    {
        if (! $this->viewingList) {
            return;
        }

        if ($type === 'student') {
            $student = Student::find($id);
            if ($student) {
                $this->viewingList->removeStudent($student);
            }
        } else {
            $user = User::find($id);
            if ($user) {
                $this->viewingList->removeUser($user);
            }
        }

        $this->viewingList->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Member removed from list.',
        ]);
    }

    // ============================================
    // COMPUTED PROPERTIES
    // ============================================

    public function getListsProperty()
    {
        $user = auth()->user();

        $query = ContactList::where('org_id', $user->org_id)
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('list_type', $this->filterType);
        }

        return $query->paginate(15);
    }

    public function getAvailableGradesProperty(): array
    {
        $user = auth()->user();

        return Student::where('org_id', $user->org_id)
            ->whereNotNull('grade_level')
            ->distinct()
            ->pluck('grade_level')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getAvailableClassroomsProperty()
    {
        $user = auth()->user();

        return Classroom::where('org_id', $user->org_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getRiskLevelsProperty(): array
    {
        return ['high', 'medium', 'low'];
    }

    public function getAvailableMembersProperty()
    {
        if (! $this->viewingList) {
            return collect();
        }

        $user = auth()->user();
        $search = $this->memberSearch;

        if ($this->viewingList->list_type === ContactList::TYPE_STUDENT || $this->viewingList->list_type === ContactList::TYPE_MIXED) {
            $students = Student::where('org_id', $user->org_id)
                ->whereNull('deleted_at')
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                })
                ->with('user')
                ->limit(20)
                ->get()
                ->map(fn ($s) => [
                    'key' => 'student:'.$s->id,
                    'type' => 'student',
                    'id' => $s->id,
                    'name' => $s->full_name,
                    'meta' => 'Grade '.$s->grade_level,
                ]);

            if ($this->viewingList->list_type === ContactList::TYPE_STUDENT) {
                return $students;
            }
        }

        if ($this->viewingList->list_type === ContactList::TYPE_TEACHER || $this->viewingList->list_type === ContactList::TYPE_MIXED) {
            $users = User::where('org_id', $user->org_id)
                ->whereNull('deleted_at')
                ->when($search, function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get()
                ->map(fn ($u) => [
                    'key' => 'user:'.$u->id,
                    'type' => 'user',
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'meta' => ucfirst($u->role),
                ]);

            if ($this->viewingList->list_type === ContactList::TYPE_TEACHER) {
                return $users;
            }

            // Mixed - combine both
            return collect($students ?? [])->merge($users);
        }

        return collect();
    }

    public function render()
    {
        return view('livewire.contact-list-manager', [
            'lists' => $this->lists,
            'availableGrades' => $this->availableGrades,
            'availableClassrooms' => $this->availableClassrooms,
            'riskLevels' => $this->riskLevels,
            'availableMembers' => $this->showMembersModal ? $this->availableMembers : collect(),
        ])->layout('layouts.dashboard', ['title' => 'Contact Lists']);
    }
}
