<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseSuggestion;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Learner;
use App\Services\ProviderMatchingService;
use Livewire\Component;
use Livewire\WithPagination;

class ContactResources extends Component
{
    use WithPagination;

    public string $contactType;

    public int $contactId;

    // Active tab
    public string $activeTab = 'assigned';

    // Assign resource modal
    public bool $showAssignModal = false;

    public ?int $selectedResourceId = null;

    public string $assignmentNotes = '';

    public string $searchResources = '';

    // Enroll in course modal
    public bool $showEnrollModal = false;

    public ?int $selectedCourseId = null;

    public string $searchCourses = '';

    // Expanded assignment tracking
    public ?int $expandedAssignmentId = null;

    public ?int $expandedEnrollmentId = null;

    // Edit mode
    public ?int $editingAssignmentId = null;

    public ?int $editingProgress = null;

    public string $editingStatus = '';

    public string $editingNotes = '';

    // Filter
    public string $filterStatus = 'all';

    public string $enrollmentFilterStatus = 'all';

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openAssignModal(): void
    {
        $this->showAssignModal = true;
        $this->selectedResourceId = null;
        $this->assignmentNotes = '';
        $this->searchResources = '';
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->selectedResourceId = null;
        $this->assignmentNotes = '';
        $this->searchResources = '';
    }

    public function assignResource(): void
    {
        $this->validate([
            'selectedResourceId' => 'required|exists:resources,id',
        ]);

        $user = auth()->user();

        $assignment = ResourceAssignment::create([
            'resource_id' => $this->selectedResourceId,
            'learner_id' => $this->contactId,
            'assigned_by' => $user->id,
            'status' => 'pending',
            'notes' => $this->assignmentNotes ?: null,
            'assigned_at' => now(),
            'progress_percent' => 0,
        ]);

        AuditLog::log('create', $assignment);

        $this->closeAssignModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assigned successfully.',
        ]);
    }

    public function toggleExpand(int $assignmentId): void
    {
        if ($this->expandedAssignmentId === $assignmentId) {
            $this->expandedAssignmentId = null;
        } else {
            $this->expandedAssignmentId = $assignmentId;
            $this->editingAssignmentId = null;
        }
    }

    public function startEdit(int $assignmentId): void
    {
        $assignment = ResourceAssignment::findOrFail($assignmentId);

        $this->editingAssignmentId = $assignmentId;
        $this->expandedAssignmentId = $assignmentId;
        $this->editingProgress = $assignment->progress_percent ?? 0;
        $this->editingStatus = $assignment->status ?? 'pending';
        $this->editingNotes = $assignment->notes ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingAssignmentId = null;
        $this->editingProgress = null;
        $this->editingStatus = '';
        $this->editingNotes = '';
    }

    public function saveChanges(): void
    {
        $assignment = ResourceAssignment::findOrFail($this->editingAssignmentId);

        $oldValues = $assignment->only(['progress_percent', 'status', 'notes']);

        $updateData = [
            'progress_percent' => $this->editingProgress,
            'status' => $this->editingStatus,
            'notes' => $this->editingNotes ?: null,
        ];

        // Auto-update timestamps based on status
        if ($this->editingStatus === 'in_progress' && ! $assignment->started_at) {
            $updateData['started_at'] = now();
        }
        if ($this->editingStatus === 'completed' && ! $assignment->completed_at) {
            $updateData['completed_at'] = now();
            $updateData['progress_percent'] = 100;
        }

        $assignment->update($updateData);

        AuditLog::log('update', $assignment, $oldValues, $updateData);

        $this->cancelEdit();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assignment updated.',
        ]);
    }

    public function removeAssignment(int $assignmentId): void
    {
        $assignment = ResourceAssignment::findOrFail($assignmentId);

        AuditLog::log('delete', $assignment);
        $assignment->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assignment removed.',
        ]);
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function getAssignmentsProperty()
    {
        $query = ResourceAssignment::where('learner_id', $this->contactId)
            ->with(['resource', 'assigner'])
            ->orderByDesc('assigned_at');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(10);
    }

    public function getAvailableResourcesProperty()
    {
        $user = auth()->user();

        $query = Resource::forOrganization($user->org_id)
            ->active()
            ->orderBy('title');

        if ($this->searchResources) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->searchResources.'%')
                    ->orWhere('description', 'like', '%'.$this->searchResources.'%');
            });
        }

        return $query->get();
    }

    // ========================================
    // Course Enrollment Methods
    // ========================================

    public function openEnrollModal(): void
    {
        $this->showEnrollModal = true;
        $this->selectedCourseId = null;
        $this->searchCourses = '';
    }

    public function closeEnrollModal(): void
    {
        $this->showEnrollModal = false;
        $this->selectedCourseId = null;
        $this->searchCourses = '';
    }

    public function enrollInCourse(): void
    {
        $this->validate([
            'selectedCourseId' => 'required|exists:mini_courses,id',
        ]);

        $user = auth()->user();
        $course = MiniCourse::findOrFail($this->selectedCourseId);

        // Check if already enrolled
        $existingEnrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
            ->where('learner_id', $this->contactId)
            ->whereIn('status', [
                MiniCourseEnrollment::STATUS_ENROLLED,
                MiniCourseEnrollment::STATUS_IN_PROGRESS,
            ])
            ->first();

        if ($existingEnrollment) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Learner is already enrolled in this course.',
            ]);

            return;
        }

        $enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $course->id,
            'mini_course_version_id' => $course->current_version_id,
            'learner_id' => $this->contactId,
            'enrolled_by' => $user->id,
            'enrollment_source' => MiniCourseEnrollment::SOURCE_MANUAL,
            'status' => MiniCourseEnrollment::STATUS_ENROLLED,
        ]);

        AuditLog::log('create', $enrollment);

        $this->closeEnrollModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Learner enrolled in course successfully.',
        ]);
    }

    public function toggleEnrollmentExpand(int $enrollmentId): void
    {
        if ($this->expandedEnrollmentId === $enrollmentId) {
            $this->expandedEnrollmentId = null;
        } else {
            $this->expandedEnrollmentId = $enrollmentId;
        }
    }

    public function pauseEnrollment(int $enrollmentId): void
    {
        $enrollment = MiniCourseEnrollment::findOrFail($enrollmentId);
        $oldStatus = $enrollment->status;

        $enrollment->update(['status' => MiniCourseEnrollment::STATUS_PAUSED]);

        AuditLog::log('update', $enrollment, ['status' => $oldStatus], ['status' => 'paused']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Enrollment paused.',
        ]);
    }

    public function resumeEnrollment(int $enrollmentId): void
    {
        $enrollment = MiniCourseEnrollment::findOrFail($enrollmentId);
        $oldStatus = $enrollment->status;

        $newStatus = $enrollment->started_at
            ? MiniCourseEnrollment::STATUS_IN_PROGRESS
            : MiniCourseEnrollment::STATUS_ENROLLED;

        $enrollment->update(['status' => $newStatus]);

        AuditLog::log('update', $enrollment, ['status' => $oldStatus], ['status' => $newStatus]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Enrollment resumed.',
        ]);
    }

    public function withdrawEnrollment(int $enrollmentId): void
    {
        $enrollment = MiniCourseEnrollment::findOrFail($enrollmentId);
        $oldStatus = $enrollment->status;

        $enrollment->update([
            'status' => MiniCourseEnrollment::STATUS_WITHDRAWN,
            'feedback' => array_merge($enrollment->feedback ?? [], [
                'withdrawn_at' => now()->toISOString(),
                'withdrawn_by' => auth()->id(),
            ]),
        ]);

        AuditLog::log('update', $enrollment, ['status' => $oldStatus], ['status' => 'withdrawn']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Learner withdrawn from course.',
        ]);
    }

    public function setEnrollmentFilterStatus(string $status): void
    {
        $this->enrollmentFilterStatus = $status;
        $this->resetPage();
    }

    public function getEnrollmentsProperty()
    {
        $query = MiniCourseEnrollment::where('learner_id', $this->contactId)
            ->with(['miniCourse', 'currentStep', 'stepProgress'])
            ->orderByDesc('created_at');

        if ($this->enrollmentFilterStatus !== 'all') {
            $query->where('status', $this->enrollmentFilterStatus);
        }

        return $query->paginate(10);
    }

    public function getAvailableCoursesProperty()
    {
        $user = auth()->user();

        $query = MiniCourse::forOrganization($user->org_id)
            ->active()
            ->orderBy('title');

        if ($this->searchCourses) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->searchCourses.'%')
                    ->orWhere('description', 'like', '%'.$this->searchCourses.'%');
            });
        }

        return $query->get();
    }

    // ========================================
    // Course Suggestions Methods
    // ========================================

    public function acceptSuggestion(int $suggestionId): void
    {
        $suggestion = MiniCourseSuggestion::findOrFail($suggestionId);
        $user = auth()->user();

        $enrollment = $suggestion->accept($user->id);

        AuditLog::log('update', $suggestion, ['status' => 'pending'], ['status' => 'accepted']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Suggestion accepted and learner enrolled.',
        ]);
    }

    public function declineSuggestion(int $suggestionId, string $reason = ''): void
    {
        $suggestion = MiniCourseSuggestion::findOrFail($suggestionId);
        $user = auth()->user();

        $suggestion->decline($user->id, $reason);

        AuditLog::log('update', $suggestion, ['status' => 'pending'], ['status' => 'declined']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Suggestion declined.',
        ]);
    }

    public function getCourseSuggestionsProperty()
    {
        return MiniCourseSuggestion::where('contact_type', Learner::class)
            ->where('contact_id', $this->contactId)
            ->where('status', MiniCourseSuggestion::STATUS_PENDING)
            ->with(['miniCourse'])
            ->orderByDesc('relevance_score')
            ->get();
    }

    // ========================================
    // Provider Recommendations Methods
    // ========================================

    public function getProviderRecommendationsProperty()
    {
        if ($this->contactType !== 'learner') {
            return collect();
        }

        $learner = Learner::find($this->contactId);
        if (! $learner) {
            return collect();
        }

        $service = app(ProviderMatchingService::class);

        return $service->findMatchingProviders($learner, [], 5);
    }

    // ========================================
    // Program Recommendations Methods
    // ========================================

    public function getProgramRecommendationsProperty()
    {
        if ($this->contactType !== 'learner') {
            return collect();
        }

        $learner = Learner::find($this->contactId);
        if (! $learner) {
            return collect();
        }

        $service = app(ProviderMatchingService::class);

        return $service->findMatchingPrograms($learner, [], 5);
    }

    public function render()
    {
        return view('livewire.contact-resources', [
            'assignments' => $this->assignments,
            'availableResources' => $this->availableResources,
            'enrollments' => $this->enrollments,
            'availableCourses' => $this->availableCourses,
            'courseSuggestions' => $this->courseSuggestions,
            'providerRecommendations' => $this->providerRecommendations,
            'programRecommendations' => $this->programRecommendations,
        ]);
    }
}
