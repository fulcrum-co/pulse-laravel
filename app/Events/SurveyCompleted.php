<?php

namespace App\Events;

use App\Models\SurveyAttempt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SurveyAttempt $attempt
    ) {}

    /**
     * Get the survey attempt.
     */
    public function getAttempt(): SurveyAttempt
    {
        return $this->attempt;
    }

    /**
     * Get the survey.
     */
    public function getSurvey()
    {
        return $this->attempt->survey;
    }

    /**
     * Get the organization ID.
     */
    public function getOrgId(): int
    {
        return $this->attempt->survey->org_id;
    }

    /**
     * Get the responses.
     */
    public function getResponses(): array
    {
        return $this->attempt->responses ?? [];
    }

    /**
     * Get the overall score.
     */
    public function getScore(): ?float
    {
        return $this->attempt->overall_score;
    }

    /**
     * Get the risk level.
     */
    public function getRiskLevel(): ?string
    {
        return $this->attempt->risk_level;
    }

    /**
     * Check if high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->attempt->risk_level === 'high';
    }

    /**
     * Get event data for workflow evaluation.
     */
    public function toWorkflowData(): array
    {
        $survey = $this->attempt->survey;
        $respondent = $this->attempt->getRespondent();

        return [
            'event_type' => 'survey_completed',
            'survey_id' => $survey->id,
            'survey_title' => $survey->title,
            'survey_type' => $survey->survey_type,
            'attempt_id' => $this->attempt->id,
            'respondent_type' => $this->attempt->learner_id ? 'learner' : 'user',
            'respondent_id' => $this->attempt->learner_id ?? $this->attempt->user_id,
            'respondent_name' => $this->attempt->respondent_name,
            'responses' => $this->attempt->responses,
            'overall_score' => $this->attempt->overall_score,
            'risk_level' => $this->attempt->risk_level,
            'response_channel' => $this->attempt->response_channel,
            'completed_at' => $this->attempt->completed_at?->toIso8601String(),
            'duration_seconds' => $this->attempt->duration_seconds,
            'org_id' => $survey->org_id,
        ];
    }
}
