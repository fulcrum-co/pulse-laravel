<?php

namespace App\Livewire;

use App\Models\SurveyAttempt;
use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class ContactSurveys extends Component
{
    use WithPagination;

    public string $contactType;
    public int $contactId;

    // Expanded survey tracking
    public ?int $expandedAttemptId = null;

    // Edit mode
    public ?int $editingAttemptId = null;
    public array $editingResponses = [];
    public ?float $editingScore = null;

    // Filter
    public string $filterStatus = 'all';

    protected $listeners = ['edit-survey-attempt' => 'handleEditFromTimeline'];

    public function handleEditFromTimeline(int $attemptId): void
    {
        $this->startEdit($attemptId);
    }

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
    }

    public function toggleExpand(int $attemptId): void
    {
        if ($this->expandedAttemptId === $attemptId) {
            $this->expandedAttemptId = null;
        } else {
            $this->expandedAttemptId = $attemptId;
            $this->editingAttemptId = null;
        }
    }

    public function startEdit(int $attemptId): void
    {
        $attempt = SurveyAttempt::findOrFail($attemptId);

        $this->editingAttemptId = $attemptId;
        $this->expandedAttemptId = $attemptId;
        $this->editingResponses = $attempt->responses ?? [];
        $this->editingScore = $attempt->overall_score;
    }

    public function cancelEdit(): void
    {
        $this->editingAttemptId = null;
        $this->editingResponses = [];
        $this->editingScore = null;
    }

    public function updateResponse(string $questionId, $value): void
    {
        $this->editingResponses[$questionId] = $value;
    }

    public function saveChanges(): void
    {
        $attempt = SurveyAttempt::findOrFail($this->editingAttemptId);

        $oldValues = [
            'responses' => $attempt->responses,
            'overall_score' => $attempt->overall_score,
        ];

        // Recalculate score if responses changed
        $newScore = $this->editingScore;
        if ($attempt->survey && $this->editingResponses !== $attempt->responses) {
            $calculatedScore = $attempt->survey->calculateScore($this->editingResponses);
            if ($calculatedScore !== null) {
                $newScore = $calculatedScore;
            }
        }

        $attempt->update([
            'responses' => $this->editingResponses,
            'overall_score' => $newScore,
        ]);

        AuditLog::log('update', $attempt, $oldValues, [
            'responses' => $this->editingResponses,
            'overall_score' => $newScore,
        ]);

        $this->cancelEdit();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Survey responses updated successfully.',
        ]);
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function getAttemptsProperty()
    {
        $query = SurveyAttempt::where('student_id', $this->contactId)
            ->with(['survey'])
            ->orderByDesc('created_at');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.contact-surveys', [
            'attempts' => $this->attempts,
        ]);
    }
}
