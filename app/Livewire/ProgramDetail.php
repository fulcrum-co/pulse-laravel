<?php

namespace App\Livewire;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\Student;
use App\Models\ContactList;
use Livewire\Component;

class ProgramDetail extends Component
{
    public Program $program;

    // Enroll modal state
    public bool $showEnrollModal = false;
    public string $enrollType = 'student'; // student or list
    public ?int $selectedStudentId = null;
    public ?int $selectedListId = null;
    public string $enrollNote = '';

    public function mount(Program $program): void
    {
        // Ensure the user has access to this program's organization
        if (!auth()->user()->canAccessOrganization($program->org_id)) {
            abort(403);
        }

        $this->program = $program;
    }

    /**
     * Check if the current user can push content to downstream organizations.
     */
    public function getCanPushProperty(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        return $hasDownstream || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Open the push modal for this program.
     */
    public function openPushModal(): void
    {
        $this->dispatch('openPushProgram', $this->program->id);
    }

    /**
     * Open the enroll modal.
     */
    public function openEnrollModal(): void
    {
        $this->resetEnrollForm();
        $this->showEnrollModal = true;
    }

    /**
     * Close the enroll modal.
     */
    public function closeEnrollModal(): void
    {
        $this->showEnrollModal = false;
        $this->resetEnrollForm();
    }

    /**
     * Reset the enroll form.
     */
    protected function resetEnrollForm(): void
    {
        $this->enrollType = 'student';
        $this->selectedStudentId = null;
        $this->selectedListId = null;
        $this->enrollNote = '';
    }

    /**
     * Enroll a student or list in this program.
     */
    public function enrollStudent(): void
    {
        $user = auth()->user();

        if ($this->enrollType === 'student') {
            $this->validate([
                'selectedStudentId' => 'required|exists:students,id',
            ]);

            // Check for existing enrollment
            $exists = ProgramEnrollment::where('program_id', $this->program->id)
                ->where('student_id', $this->selectedStudentId)
                ->whereIn('status', ['enrolled', 'active'])
                ->exists();

            if ($exists) {
                session()->flash('error', 'This student is already enrolled in this program.');
                return;
            }

            ProgramEnrollment::create([
                'program_id' => $this->program->id,
                'student_id' => $this->selectedStudentId,
                'enrolled_by' => $user->id,
                'notes' => $this->enrollNote ?: null,
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]);

            // Increment enrollment count on program
            $this->program->incrementEnrollment();

            session()->flash('success', 'Student enrolled in program successfully.');

        } elseif ($this->enrollType === 'list') {
            $this->validate([
                'selectedListId' => 'required|exists:contact_lists,id',
            ]);

            $list = ContactList::find($this->selectedListId);
            $students = $list->students;
            $count = 0;

            foreach ($students as $student) {
                // Avoid duplicate enrollments
                $exists = ProgramEnrollment::where('program_id', $this->program->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['enrolled', 'active'])
                    ->exists();

                if (!$exists) {
                    ProgramEnrollment::create([
                        'program_id' => $this->program->id,
                        'student_id' => $student->id,
                        'enrolled_by' => $user->id,
                        'notes' => $this->enrollNote ?: null,
                        'enrolled_at' => now(),
                        'status' => 'enrolled',
                    ]);
                    $this->program->incrementEnrollment();
                    $count++;
                }
            }

            session()->flash('success', "{$count} students enrolled in program from the list.");
        }

        $this->closeEnrollModal();
    }

    /**
     * Get accessible students for enrollment.
     */
    public function getStudentsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return Student::whereIn('org_id', $accessibleOrgIds)
            ->with('user')
            ->get()
            ->sortBy(fn ($student) => $student->user?->name ?? '')
            ->values();
    }

    /**
     * Get contact lists for enrollment.
     */
    public function getContactListsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return ContactList::whereIn('org_id', $accessibleOrgIds)
            ->whereIn('list_type', ['student', 'mixed'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get enrollment count for this program.
     */
    public function getEnrollmentCountProperty(): int
    {
        return $this->program->enrollments()->count();
    }

    public function render()
    {
        return view('livewire.program-detail', [
            'canPush' => $this->canPush,
            'students' => $this->students,
            'contactLists' => $this->contactLists,
            'enrollmentCount' => $this->enrollmentCount,
        ])->layout('layouts.dashboard', ['title' => $this->program->name]);
    }
}
