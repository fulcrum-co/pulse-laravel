<?php

namespace App\Livewire\Cohorts;

use App\Models\Cohort;
use App\Models\CohortMember;
use App\Models\User;
use App\Services\TerminologyService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CohortEnrollment extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Cohort $cohort;

    // Search and filter
    public string $userSearch = '';
    public array $selectedUsers = [];

    // Bulk enrollment
    public string $bulkRole = CohortMember::ROLE_STUDENT;
    public bool $sendWelcomeEmail = true;

    // CSV import
    public $csvFile = null;
    public array $csvPreview = [];
    public bool $showCsvPreview = false;
    public array $csvErrors = [];

    protected TerminologyService $terminology;

    protected $rules = [
        'csvFile' => 'nullable|file|mimes:csv,txt|max:2048',
    ];

    public function boot(TerminologyService $terminology): void
    {
        $this->terminology = $terminology;
    }

    public function mount(Cohort $cohort): void
    {
        $this->cohort = $cohort;
    }

    public function updatedUserSearch(): void
    {
        $this->resetPage();
    }

    public function toggleUser(int $userId): void
    {
        if (in_array($userId, $this->selectedUsers)) {
            $this->selectedUsers = array_values(array_diff($this->selectedUsers, [$userId]));
        } else {
            $this->selectedUsers[] = $userId;
        }
    }

    public function selectAll(): void
    {
        $users = $this->getSearchResults();
        $this->selectedUsers = array_unique(array_merge(
            $this->selectedUsers,
            $users->pluck('id')->toArray()
        ));
    }

    public function clearSelection(): void
    {
        $this->selectedUsers = [];
    }

    public function enrollSelected(): void
    {
        if (empty($this->selectedUsers)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please select at least one user to enroll.',
            ]);
            return;
        }

        $enrolled = 0;
        $skipped = 0;

        DB::transaction(function () use (&$enrolled, &$skipped) {
            foreach ($this->selectedUsers as $userId) {
                // Check if already enrolled
                $existing = CohortMember::where('cohort_id', $this->cohort->id)
                    ->where('user_id', $userId)
                    ->exists();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                CohortMember::create([
                    'cohort_id' => $this->cohort->id,
                    'user_id' => $userId,
                    'role' => $this->bulkRole,
                    'status' => CohortMember::STATUS_ENROLLED,
                    'enrollment_source' => CohortMember::SOURCE_ADMIN_ENROLLED,
                    'enrolled_at' => now(),
                ]);

                $enrolled++;

                // Send welcome email if enabled
                if ($this->sendWelcomeEmail) {
                    // TODO: Dispatch welcome email job
                    // dispatch(new SendCohortWelcomeEmail($userId, $this->cohort->id));
                }
            }
        });

        $this->selectedUsers = [];
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Enrolled {$enrolled} " . $this->terminology->get('learner_plural') .
                ($skipped > 0 ? " ({$skipped} already enrolled)" : ''),
        ]);
    }

    public function updatedCsvFile(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $this->parseCsvPreview();
    }

    protected function parseCsvPreview(): void
    {
        $this->csvPreview = [];
        $this->csvErrors = [];
        $this->showCsvPreview = false;

        if (!$this->csvFile) {
            return;
        }

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->csvErrors[] = 'Unable to read file.';
            return;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            $this->csvErrors[] = 'File is empty or invalid.';
            fclose($handle);
            return;
        }

        // Normalize header
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        // Check for required column
        if (!in_array('email', $header)) {
            $this->csvErrors[] = 'CSV must contain an "email" column.';
            fclose($handle);
            return;
        }

        $emailIndex = array_search('email', $header);
        $nameIndex = array_search('name', $header);
        $firstNameIndex = array_search('first_name', $header);
        $lastNameIndex = array_search('last_name', $header);
        $roleIndex = array_search('role', $header);

        $rows = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $email = trim($row[$emailIndex] ?? '');

            if (empty($email)) {
                $this->csvErrors[] = "Row {$lineNumber}: Missing email.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->csvErrors[] = "Row {$lineNumber}: Invalid email '{$email}'.";
                continue;
            }

            // Check if user exists
            $user = User::where('email', $email)->first();
            $existingMember = $user
                ? CohortMember::where('cohort_id', $this->cohort->id)->where('user_id', $user->id)->exists()
                : false;

            $role = CohortMember::ROLE_STUDENT;
            if ($roleIndex !== false && !empty($row[$roleIndex])) {
                $role = strtolower(trim($row[$roleIndex]));
                if (!in_array($role, array_keys(CohortMember::getRoleOptions()))) {
                    $role = CohortMember::ROLE_STUDENT;
                }
            }

            $name = '';
            if ($nameIndex !== false) {
                $name = trim($row[$nameIndex] ?? '');
            } elseif ($firstNameIndex !== false || $lastNameIndex !== false) {
                $firstName = trim($row[$firstNameIndex] ?? '');
                $lastName = trim($row[$lastNameIndex] ?? '');
                $name = trim("{$firstName} {$lastName}");
            }

            $rows[] = [
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'user_exists' => (bool) $user,
                'user_id' => $user?->id,
                'already_enrolled' => $existingMember,
            ];

            if (count($rows) >= 100) {
                break; // Preview limit
            }
        }

        fclose($handle);
        $this->csvPreview = $rows;
        $this->showCsvPreview = true;
    }

    public function importCsv(): void
    {
        if (empty($this->csvPreview)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'No valid records to import.',
            ]);
            return;
        }

        $enrolled = 0;
        $skipped = 0;
        $invited = 0;

        DB::transaction(function () use (&$enrolled, &$skipped, &$invited) {
            foreach ($this->csvPreview as $row) {
                if ($row['already_enrolled']) {
                    $skipped++;
                    continue;
                }

                if ($row['user_exists'] && $row['user_id']) {
                    CohortMember::create([
                        'cohort_id' => $this->cohort->id,
                        'user_id' => $row['user_id'],
                        'role' => $row['role'],
                        'status' => CohortMember::STATUS_ENROLLED,
                        'enrollment_source' => CohortMember::SOURCE_CSV_IMPORT,
                        'enrolled_at' => now(),
                    ]);
                    $enrolled++;

                    if ($this->sendWelcomeEmail) {
                        // TODO: Dispatch welcome email
                    }
                } else {
                    // TODO: Create invitation for non-existing users
                    $invited++;
                }
            }
        });

        $this->csvFile = null;
        $this->csvPreview = [];
        $this->showCsvPreview = false;

        $message = "Enrolled {$enrolled} " . $this->terminology->get('learner_plural');
        if ($skipped > 0) {
            $message .= ", {$skipped} already enrolled";
        }
        if ($invited > 0) {
            $message .= ", {$invited} invitations pending";
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function cancelCsvImport(): void
    {
        $this->csvFile = null;
        $this->csvPreview = [];
        $this->showCsvPreview = false;
        $this->csvErrors = [];
    }

    protected function getSearchResults()
    {
        if (strlen($this->userSearch) < 2) {
            return collect();
        }

        // Get already enrolled user IDs
        $enrolledIds = CohortMember::where('cohort_id', $this->cohort->id)
            ->pluck('user_id')
            ->toArray();

        return User::query()
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->userSearch}%")
                    ->orWhere('last_name', 'like', "%{$this->userSearch}%")
                    ->orWhere('email', 'like', "%{$this->userSearch}%");
            })
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('first_name')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        $searchResults = $this->getSearchResults();

        // Current members for reference
        $currentMembers = $this->cohort->members()
            ->with('user')
            ->latest('enrolled_at')
            ->limit(10)
            ->get();

        return view('livewire.cohorts.cohort-enrollment', [
            'searchResults' => $searchResults,
            'currentMembers' => $currentMembers,
            'roleOptions' => CohortMember::getRoleOptions(),
            'term' => $this->terminology,
        ])->layout('components.layouts.dashboard');
    }
}
