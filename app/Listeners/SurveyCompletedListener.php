<?php

namespace App\Listeners;

use App\Events\SurveyCompleted;
use App\Models\Workflow;
use App\Jobs\ProcessWorkflow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SurveyCompletedListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SurveyCompleted $event): void
    {
        $attempt = $event->getAttempt();
        $survey = $event->getSurvey();
        $orgId = $event->getOrgId();

        Log::info('Survey completed, checking for workflows', [
            'survey_id' => $survey->id,
            'attempt_id' => $attempt->id,
            'risk_level' => $attempt->risk_level,
        ]);

        // Find workflows triggered by survey completion
        $workflows = Workflow::where('org_id', $orgId)
            ->where('status', 'active')
            ->where('trigger_type', 'survey_response')
            ->get();

        foreach ($workflows as $workflow) {
            if ($this->shouldTriggerWorkflow($workflow, $event)) {
                Log::info('Triggering workflow for survey completion', [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->name,
                    'survey_id' => $survey->id,
                ]);

                ProcessWorkflow::dispatch($workflow, $event->toWorkflowData());
            }
        }
    }

    /**
     * Determine if the workflow should be triggered based on its configuration.
     */
    protected function shouldTriggerWorkflow(Workflow $workflow, SurveyCompleted $event): bool
    {
        $config = $workflow->trigger_config ?? [];
        $attempt = $event->getAttempt();
        $survey = $event->getSurvey();

        // Check if workflow is configured for this specific survey
        if (isset($config['survey_id']) && $config['survey_id'] !== $survey->id) {
            return false;
        }

        // Check if workflow is configured for this survey type
        if (isset($config['survey_type']) && $config['survey_type'] !== $survey->survey_type) {
            return false;
        }

        // Check risk level conditions
        if (isset($config['risk_level'])) {
            $requiredRisk = is_array($config['risk_level'])
                ? $config['risk_level']
                : [$config['risk_level']];

            if (!in_array($attempt->risk_level, $requiredRisk)) {
                return false;
            }
        }

        // Check score threshold conditions
        if (isset($config['score_threshold'])) {
            $score = $attempt->overall_score;
            $threshold = $config['score_threshold'];
            $operator = $config['score_operator'] ?? '<=';

            if ($score === null) {
                return false;
            }

            $passes = match ($operator) {
                '<' => $score < $threshold,
                '<=' => $score <= $threshold,
                '>' => $score > $threshold,
                '>=' => $score >= $threshold,
                '=' => $score == $threshold,
                '!=' => $score != $threshold,
                default => false,
            };

            if (!$passes) {
                return false;
            }
        }

        // Check specific answer conditions
        if (isset($config['answer_conditions'])) {
            foreach ($config['answer_conditions'] as $condition) {
                $questionId = $condition['question_id'];
                $expectedValue = $condition['value'];
                $operator = $condition['operator'] ?? '=';

                $actualValue = $attempt->responses[$questionId] ?? null;

                if ($actualValue === null) {
                    return false;
                }

                $passes = match ($operator) {
                    '=' => $actualValue == $expectedValue,
                    '!=' => $actualValue != $expectedValue,
                    '<' => is_numeric($actualValue) && $actualValue < $expectedValue,
                    '<=' => is_numeric($actualValue) && $actualValue <= $expectedValue,
                    '>' => is_numeric($actualValue) && $actualValue > $expectedValue,
                    '>=' => is_numeric($actualValue) && $actualValue >= $expectedValue,
                    'contains' => is_string($actualValue) && str_contains(strtolower($actualValue), strtolower($expectedValue)),
                    'in' => is_array($expectedValue) && in_array($actualValue, $expectedValue),
                    default => false,
                };

                if (!$passes) {
                    return false;
                }
            }
        }

        return true;
    }
}
