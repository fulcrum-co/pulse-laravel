<?php

namespace App\Livewire;

use App\Models\Survey;
use App\Models\StrategySurveyAssignment;
use Livewire\Component;

class SurveyAssignmentModal extends Component
{
    public $show = false;
    public $assignableType = '';
    public $assignableId = null;
    public $search = '';

    protected $listeners = ['openSurveyAssignment' => 'open'];

    public function open($type, $id)
    {
        $this->assignableType = $type;
        $this->assignableId = $id;
        $this->search = '';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->assignableType = '';
        $this->assignableId = null;
        $this->search = '';
    }

    public function assignSurvey($surveyId)
    {
        // Check if already assigned
        $exists = StrategySurveyAssignment::where('survey_id', $surveyId)
            ->where('assignable_type', $this->getModelClass())
            ->where('assignable_id', $this->assignableId)
            ->exists();

        if (!$exists) {
            StrategySurveyAssignment::create([
                'survey_id' => $surveyId,
                'assignable_type' => $this->getModelClass(),
                'assignable_id' => $this->assignableId,
            ]);

            $this->dispatch('refreshPlanner');
        }
    }

    public function removeSurvey($surveyId)
    {
        StrategySurveyAssignment::where('survey_id', $surveyId)
            ->where('assignable_type', $this->getModelClass())
            ->where('assignable_id', $this->assignableId)
            ->delete();

        $this->dispatch('refreshPlanner');
    }

    protected function getModelClass(): string
    {
        return match($this->assignableType) {
            'focus_area' => 'App\\Models\\FocusArea',
            'objective' => 'App\\Models\\Objective',
            'activity' => 'App\\Models\\Activity',
            default => '',
        };
    }

    public function getAvailableSurveysProperty()
    {
        $user = auth()->user();

        $query = Survey::where('org_id', $user->org_id)
            ->where('status', 'active');

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return $query->limit(20)->get();
    }

    public function getAssignedSurveysProperty()
    {
        if (!$this->assignableId) {
            return collect();
        }

        return StrategySurveyAssignment::where('assignable_type', $this->getModelClass())
            ->where('assignable_id', $this->assignableId)
            ->with('survey')
            ->get()
            ->pluck('survey');
    }

    public function render()
    {
        return view('livewire.survey-assignment-modal', [
            'availableSurveys' => $this->availableSurveys,
            'assignedSurveys' => $this->assignedSurveys,
        ]);
    }
}
