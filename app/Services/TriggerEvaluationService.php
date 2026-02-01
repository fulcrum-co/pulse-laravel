<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SurveyAttempt;
use App\Models\Trigger;
use App\Models\TriggerLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TriggerEvaluationService
{
    protected SinchService $sinchService;

    public function __construct(SinchService $sinchService)
    {
        $this->sinchService = $sinchService;
    }

    /**
     * Evaluate all triggers for a student based on survey data.
     */
    public function evaluateForStudent(Student $student, SurveyAttempt $attempt): array
    {
        $triggeredLogs = [];

        // Get active triggers for this organization (and ancestors)
        $orgIds = array_merge(
            [$student->org_id],
            $student->school?->ancestor_org_ids ?? []
        );

        $triggers = Trigger::active()
            ->whereIn('org_id', $orgIds)
            ->get();

        foreach ($triggers as $trigger) {
            // Skip if in cooldown
            if ($trigger->isInCooldown()) {
                continue;
            }

            // Evaluate trigger conditions
            if ($this->evaluateConditions($trigger, $student, $attempt)) {
                $log = $this->executeTrigger($trigger, $student, $attempt);
                $triggeredLogs[] = $log;
            }
        }

        return $triggeredLogs;
    }

    /**
     * Evaluate trigger conditions.
     */
    protected function evaluateConditions(Trigger $trigger, Student $student, SurveyAttempt $attempt): bool
    {
        $operations = $trigger->operations ?? [];
        $condition = $trigger->operand_condition ?? 'AND';

        if (empty($operations)) {
            return false;
        }

        $results = [];

        foreach ($operations as $operation) {
            $results[] = $this->evaluateOperation($operation, $student, $attempt);
        }

        return $condition === 'AND'
            ? ! in_array(false, $results, true)
            : in_array(true, $results, true);
    }

    /**
     * Evaluate a single operation.
     */
    protected function evaluateOperation(array $operation, Student $student, SurveyAttempt $attempt): bool
    {
        $operandType = $operation['operand_type'] ?? null;
        $criteria = $operation['criteria'] ?? [];

        foreach ($criteria as $criterion) {
            $field = $criterion['field'] ?? null;
            $condition = $criterion['condition'] ?? null;
            $value = $criterion['value'] ?? null;

            $actualValue = $this->getFieldValue($operandType, $field, $student, $attempt);

            if (! $this->compareValues($actualValue, $condition, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the value of a field for comparison.
     */
    protected function getFieldValue(string $operandType, string $field, Student $student, SurveyAttempt $attempt)
    {
        switch ($operandType) {
            case 'survey_score':
                $data = $attempt->llm_extracted_data ?? [];

                return data_get($data, $field);

            case 'risk_level':
                return $attempt->risk_level;

            case 'attendance':
                $data = $attempt->llm_extracted_data['attendance'] ?? [];

                return data_get($data, $field);

            case 'behavior':
                $data = $attempt->llm_extracted_data['behavior'] ?? [];

                return data_get($data, $field);

            case 'student':
                return data_get($student->toArray(), $field);

            default:
                return null;
        }
    }

    /**
     * Compare values based on condition.
     */
    protected function compareValues($actual, string $condition, $expected): bool
    {
        switch ($condition) {
            case 'equals':
                return $actual == $expected;

            case 'not_equals':
                return $actual != $expected;

            case 'greater_than':
                return is_numeric($actual) && $actual > $expected;

            case 'less_than':
                return is_numeric($actual) && $actual < $expected;

            case 'greater_than_or_equal':
                return is_numeric($actual) && $actual >= $expected;

            case 'less_than_or_equal':
                return is_numeric($actual) && $actual <= $expected;

            case 'contains':
                return is_string($actual) && str_contains($actual, $expected);

            case 'in':
                return is_array($expected) && in_array($actual, $expected);

            case 'not_in':
                return is_array($expected) && ! in_array($actual, $expected);

            default:
                return false;
        }
    }

    /**
     * Execute trigger actions.
     */
    protected function executeTrigger(Trigger $trigger, Student $student, SurveyAttempt $attempt): TriggerLog
    {
        $actionsExecuted = [];

        foreach ($trigger->actions ?? [] as $action) {
            $result = $this->executeAction($action, $student, $attempt);
            $actionsExecuted[] = $result;
        }

        // Record trigger activation
        $trigger->recordActivation();

        // Create log
        $log = TriggerLog::create([
            'trigger_id' => $trigger->_id,
            'student_id' => $student->_id,
            'org_id' => $student->org_id,
            'triggering_event' => [
                'type' => 'survey_completed',
                'related_survey_attempt_id' => $attempt->_id,
                'conditions_met' => $trigger->operations,
            ],
            'actions_executed' => $actionsExecuted,
        ]);

        return $log;
    }

    /**
     * Execute a single action.
     */
    protected function executeAction(array $action, Student $student, SurveyAttempt $attempt): array
    {
        $actionType = $action['action_type'] ?? null;
        $events = $action['action_events'] ?? [];

        try {
            switch ($actionType) {
                case 'send_email':
                    return $this->sendEmailAction($events, $student, $attempt);

                case 'send_sms':
                    return $this->sendSmsAction($events, $student, $attempt);

                case 'send_whatsapp':
                    return $this->sendWhatsAppAction($events, $student, $attempt);

                case 'make_call':
                    return $this->makeCallAction($events, $student, $attempt);

                case 'assign_resource':
                    return $this->assignResourceAction($events, $student, $attempt);

                case 'create_task':
                    return $this->createTaskAction($events, $student, $attempt);

                default:
                    return [
                        'action_type' => $actionType,
                        'status' => 'failed',
                        'details' => ['error' => 'Unknown action type'],
                        'executed_at' => now()->toISOString(),
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Trigger action failed', [
                'action_type' => $actionType,
                'student_id' => $student->_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'action_type' => $actionType,
                'status' => 'failed',
                'details' => ['error' => $e->getMessage()],
                'executed_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Send email action.
     */
    protected function sendEmailAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        foreach ($events as $event) {
            $email = $event['action_value'] ?? null;
            if ($email) {
                // TODO: Implement email notification
                // Mail::to($email)->send(new TriggerAlertEmail($student, $attempt));
            }
        }

        return [
            'action_type' => 'send_email',
            'status' => 'success',
            'details' => ['recipients' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send SMS action.
     */
    protected function sendSmsAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        $message = "Alert: {$student->full_name} requires attention based on recent check-in. Please review in Pulse.";

        foreach ($events as $event) {
            $phone = $event['action_value'] ?? null;
            if ($phone) {
                $this->sinchService->sendSms($phone, $message);
            }
        }

        return [
            'action_type' => 'send_sms',
            'status' => 'success',
            'details' => ['recipients' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send WhatsApp action.
     */
    protected function sendWhatsAppAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        $message = "Alert: {$student->full_name} requires attention based on recent check-in. Please review in Pulse.";

        foreach ($events as $event) {
            $phone = $event['action_value'] ?? null;
            if ($phone) {
                $this->sinchService->sendWhatsApp($phone, $message);
            }
        }

        return [
            'action_type' => 'send_whatsapp',
            'status' => 'success',
            'details' => ['recipients' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Make call action.
     */
    protected function makeCallAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        $message = "This is an automated alert from Pulse. Student {$student->full_name} requires attention based on a recent check-in. Please log in to Pulse for details.";

        foreach ($events as $event) {
            $phone = $event['action_value'] ?? null;
            if ($phone) {
                $this->sinchService->initiateCall($phone, $message);
            }
        }

        return [
            'action_type' => 'make_call',
            'status' => 'success',
            'details' => ['recipients' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Assign resource action.
     */
    protected function assignResourceAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        // TODO: Implement resource assignment
        return [
            'action_type' => 'assign_resource',
            'status' => 'success',
            'details' => ['resources' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Create task action.
     */
    protected function createTaskAction(array $events, Student $student, SurveyAttempt $attempt): array
    {
        // TODO: Implement task creation
        return [
            'action_type' => 'create_task',
            'status' => 'success',
            'details' => ['tasks' => count($events)],
            'executed_at' => now()->toISOString(),
        ];
    }
}
