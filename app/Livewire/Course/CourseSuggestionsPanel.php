<?php

namespace App\Livewire\Course;

use App\Models\MiniCourse;
use App\Models\MiniCourseSuggestion;
use App\Models\Participant;
use App\Models\User;
use App\Services\AutoCourseGenerationService;
use Livewire\Component;

class CourseSuggestionsPanel extends Component
{
    public string $entityType; // 'participant' or 'instructor'

    public int $entityId;

    public bool $generating = false;

    public ?string $error = null;

    public function mount(string $entityType, int $entityId): void
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    /**
     * Generate a new AI course for this entity.
     */
    public function generateCourse(): void
    {
        $this->generating = true;
        $this->error = null;

        try {
            $service = app(AutoCourseGenerationService::class);

            if ($this->entityType === 'participant') {
                $participant = Participant::findOrFail($this->entityId);
                $course = $service->generateForLearner(
                    $participant,
                    MiniCourse::TRIGGER_MANUAL,
                    [],
                    auth()->id()
                );
            } else {
                $instructor = User::findOrFail($this->entityId);
                $course = $service->generateForTeacher(
                    $instructor,
                    MiniCourse::TRIGGER_MANUAL,
                    [],
                    auth()->id()
                );
            }

            if ($course) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'AI course generated! It\'s pending review.',
                ]);
            } else {
                $this->error = 'Failed to generate course. Please try again.';
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred while generating the course.';
            \Log::error('Course generation failed', ['error' => $e->getMessage()]);
        } finally {
            $this->generating = false;
        }
    }

    /**
     * Get existing courses for this entity.
     */
    public function getExistingCoursesProperty()
    {
        return MiniCourse::where('target_entity_type', $this->entityType)
            ->where('target_entity_id', $this->entityId)
            ->with('approvalWorkflow')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * Get pending suggestions for this entity.
     */
    public function getSuggestionsProperty()
    {
        return MiniCourseSuggestion::where('participant_id', $this->entityId)
            ->where('status', 'pending')
            ->with('miniCourse')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();
    }

    /**
     * Get active enrollments for this entity.
     */
    public function getActiveEnrollmentsProperty()
    {
        if ($this->entityType === 'participant') {
            $participant = Participant::find($this->entityId);
            if ($participant) {
                return $participant->enrollments()
                    ->whereIn('status', ['active', 'in_progress'])
                    ->with('course')
                    ->get();
            }
        }

        return collect();
    }

    /**
     * Accept a course suggestion.
     */
    public function acceptSuggestion(int $suggestionId): void
    {
        $suggestion = MiniCourseSuggestion::find($suggestionId);

        if ($suggestion) {
            $suggestion->update([
                'status' => 'accepted',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            // Enroll the participant if it's a participant suggestion
            if ($this->entityType === 'participant' && $suggestion->miniCourse) {
                $suggestion->miniCourse->enrollments()->create([
                    'participant_id' => $this->entityId,
                    'enrolled_by' => auth()->id(),
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Suggestion accepted and participant enrolled.',
            ]);
        }
    }

    /**
     * Decline a course suggestion.
     */
    public function declineSuggestion(int $suggestionId): void
    {
        $suggestion = MiniCourseSuggestion::find($suggestionId);

        if ($suggestion) {
            $suggestion->update([
                'status' => 'declined',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Suggestion declined.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.course.course-suggestions-panel', [
            'existingCourses' => $this->existingCourses,
            'suggestions' => $this->suggestions,
            'activeEnrollments' => $this->activeEnrollments,
        ]);
    }
}
