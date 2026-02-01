<?php

namespace App\Livewire\Survey;

use App\Events\SurveyCompleted;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use Livewire\Component;

class SurveyResponder extends Component
{
    public Survey $survey;

    public SurveyAttempt $attempt;

    public int $currentQuestionIndex = 0;

    public array $responses = [];

    public bool $isComplete = false;

    public bool $showWelcome = true;

    public bool $showThankYou = false;

    protected $rules = [
        'responses.*' => 'required',
    ];

    public function mount(string $surveyId, string $attemptId): void
    {
        $this->survey = Survey::findOrFail($surveyId);
        $this->attempt = SurveyAttempt::findOrFail($attemptId);

        // Load existing responses if resuming
        if ($this->attempt->responses) {
            $this->responses = $this->attempt->responses;
            $this->currentQuestionIndex = count($this->responses);
        }

        // Check if already completed
        if ($this->attempt->status === 'completed') {
            $this->isComplete = true;
            $this->showWelcome = false;
            $this->showThankYou = true;
        }
    }

    public function startSurvey(): void
    {
        $this->showWelcome = false;

        // Mark attempt as in progress
        if ($this->attempt->status === 'pending') {
            $this->attempt->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    public function submitAnswer($answer): void
    {
        $currentQuestion = $this->getCurrentQuestion();

        if (! $currentQuestion) {
            return;
        }

        // Store the response
        $this->responses[$currentQuestion['id']] = $answer;

        // Save to database
        $this->attempt->update([
            'responses' => $this->responses,
        ]);

        // Move to next question or complete
        if ($this->currentQuestionIndex < count($this->survey->questions) - 1) {
            $this->currentQuestionIndex++;
        } else {
            $this->completeSurvey();
        }
    }

    public function previousQuestion(): void
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function skipQuestion(): void
    {
        $currentQuestion = $this->getCurrentQuestion();

        if (! $currentQuestion) {
            return;
        }

        // Only allow skip if not required
        if (! ($currentQuestion['required'] ?? true)) {
            $this->responses[$currentQuestion['id']] = null;

            if ($this->currentQuestionIndex < count($this->survey->questions) - 1) {
                $this->currentQuestionIndex++;
            } else {
                $this->completeSurvey();
            }
        }
    }

    protected function completeSurvey(): void
    {
        // Calculate scores
        $scores = $this->calculateScores();

        // Update attempt
        $this->attempt->update([
            'status' => 'completed',
            'completed_at' => now(),
            'responses' => $this->responses,
            'overall_score' => $scores['overall'],
            'risk_level' => $scores['risk_level'],
        ]);

        // Fire event for workflow integration
        SurveyCompleted::dispatch($this->attempt);

        $this->isComplete = true;
        $this->showThankYou = true;
    }

    protected function calculateScores(): array
    {
        $scaleResponses = [];

        foreach ($this->survey->questions as $question) {
            $questionId = $question['id'];
            $response = $this->responses[$questionId] ?? null;

            if ($response !== null && ($question['type'] ?? 'scale') === 'scale') {
                $scaleResponses[] = (int) $response;
            }
        }

        if (empty($scaleResponses)) {
            return [
                'overall' => null,
                'risk_level' => 'low',
            ];
        }

        $average = array_sum($scaleResponses) / count($scaleResponses);

        // Determine risk level based on average (lower scores = higher risk for wellness)
        $riskLevel = match (true) {
            $average <= 2 => 'high',
            $average <= 3 => 'medium',
            default => 'low',
        };

        return [
            'overall' => round($average, 2),
            'risk_level' => $riskLevel,
        ];
    }

    public function getCurrentQuestion(): ?array
    {
        return $this->survey->questions[$this->currentQuestionIndex] ?? null;
    }

    public function getProgressProperty(): int
    {
        $total = count($this->survey->questions);
        if ($total === 0) {
            return 0;
        }

        return round(($this->currentQuestionIndex / $total) * 100);
    }

    public function render()
    {
        return view('livewire.survey.survey-responder');
    }
}
