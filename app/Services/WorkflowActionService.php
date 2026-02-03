<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Learner;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\Domain\WorkflowActionInterpolationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class WorkflowActionService
{
    public function __construct(
        protected SinchService $sinchService,
        protected WorkflowActionInterpolationService $interpolationService
    ) {}

    /**
     * Execute an action by type.
     */
    public function execute(string $actionType, array $config, array $context): array
    {
        try {
            return match ($actionType) {
                'send_email' => $this->sendEmail($config, $context),
                'send_sms' => $this->sendSms($config, $context),
                'send_whatsapp' => $this->sendWhatsApp($config, $context),
                'make_call' => $this->makeCall($config, $context),
                'webhook' => $this->sendWebhook($config, $context),
                'create_task' => $this->createTask($config, $context),
                'assign_resource' => $this->assignResource($config, $context),
                'in_app_notification' => $this->sendInAppNotification($config, $context),
                'trigger_workflow' => $this->triggerWorkflow($config, $context),
                'update_field' => $this->updateField($config, $context),
                default => $this->unknownAction($actionType),
            };
        } catch (\Exception $e) {
            Log::error('Workflow action failed', [
                'action_type' => $actionType,
                'config' => $config,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'action_type' => $actionType,
                'error' => $e->getMessage(),
                'executed_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Send email notification.
     */
    public function sendEmail(array $config, array $context): array
    {
        $recipients = $this->resolveRecipients($config['recipients'] ?? [], $context);
        $subject = $this->interpolationService->interpolateTemplate($config['subject'] ?? 'Alert Notification', $context);
        $body = $this->interpolationService->interpolateTemplate($config['body'] ?? '', $context);
        $template = $config['template'] ?? null;

        $sent = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $email = $recipient['email'] ?? $recipient;

                if ($template) {
                    // Use a Mailable class
                    Mail::to($email)->send(new \App\Mail\WorkflowAlertMail($subject, $body, $context));
                } else {
                    // Simple raw email
                    Mail::raw($body, function ($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    });
                }

                $sent++;
            } catch (\Exception $e) {
                $errors[] = ['recipient' => $email ?? 'unknown', 'error' => $e->getMessage()];
            }
        }

        return [
            'success' => $sent > 0,
            'action_type' => 'send_email',
            'details' => [
                'sent' => $sent,
                'total' => count($recipients),
                'errors' => $errors,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send SMS via Sinch.
     */
    public function sendSms(array $config, array $context): array
    {
        $recipients = $this->resolveRecipients($config['recipients'] ?? [], $context);
        $message = $this->interpolationService->interpolateTemplate($config['message'] ?? 'Alert from Pulse', $context);

        $sent = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $phone = $recipient['phone'] ?? $recipient;
                $this->sinchService->sendSms($phone, $message);
                $sent++;
            } catch (\Exception $e) {
                $errors[] = ['recipient' => $phone ?? 'unknown', 'error' => $e->getMessage()];
            }
        }

        return [
            'success' => $sent > 0,
            'action_type' => 'send_sms',
            'details' => [
                'sent' => $sent,
                'total' => count($recipients),
                'message_preview' => substr($message, 0, 100),
                'errors' => $errors,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send WhatsApp message via Sinch.
     */
    public function sendWhatsApp(array $config, array $context): array
    {
        $recipients = $this->resolveRecipients($config['recipients'] ?? [], $context);
        $message = $this->interpolationService->interpolateTemplate($config['message'] ?? 'Alert from Pulse', $context);

        $sent = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $phone = $recipient['phone'] ?? $recipient;
                $this->sinchService->sendWhatsApp($phone, $message);
                $sent++;
            } catch (\Exception $e) {
                $errors[] = ['recipient' => $phone ?? 'unknown', 'error' => $e->getMessage()];
            }
        }

        return [
            'success' => $sent > 0,
            'action_type' => 'send_whatsapp',
            'details' => [
                'sent' => $sent,
                'total' => count($recipients),
                'errors' => $errors,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Make a voice call via Sinch.
     */
    public function makeCall(array $config, array $context): array
    {
        $recipients = $this->resolveRecipients($config['recipients'] ?? [], $context);
        $message = $this->interpolationService->interpolateTemplate($config['message'] ?? 'This is an automated alert from Pulse.', $context);

        $sent = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $phone = $recipient['phone'] ?? $recipient;
                $this->sinchService->initiateCall($phone, $message);
                $sent++;
            } catch (\Exception $e) {
                $errors[] = ['recipient' => $phone ?? 'unknown', 'error' => $e->getMessage()];
            }
        }

        return [
            'success' => $sent > 0,
            'action_type' => 'make_call',
            'details' => [
                'called' => $sent,
                'total' => count($recipients),
                'errors' => $errors,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send webhook to external URL.
     */
    public function sendWebhook(array $config, array $context): array
    {
        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');
        $headers = $config['headers'] ?? [];
        $payload = $config['payload'] ?? $context;

        if (! $url) {
            return [
                'success' => false,
                'action_type' => 'webhook',
                'error' => 'No URL specified',
                'executed_at' => now()->toISOString(),
            ];
        }

        // Interpolate URL and payload
        $url = $this->interpolationService->interpolateTemplate($url, $context);
        if (is_array($payload)) {
            $payload = $this->interpolationService->interpolateArrayValues($payload, $context);
        }

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->{strtolower($method)}($url, $payload);

        return [
            'success' => $response->successful(),
            'action_type' => 'webhook',
            'details' => [
                'url' => $url,
                'method' => $method,
                'status_code' => $response->status(),
                'response_body' => $response->successful() ? substr($response->body(), 0, 500) : $response->body(),
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Create a task/follow-up.
     */
    public function createTask(array $config, array $context): array
    {
        $title = $this->interpolationService->interpolateTemplate($config['title'] ?? 'Follow-up Task', $context);
        $description = $this->interpolationService->interpolateTemplate($config['description'] ?? '', $context);
        $assigneeId = $config['assignee_id'] ?? $context['created_by'] ?? null;
        $dueDate = $config['due_date'] ?? now()->addDays(1);
        $priority = $config['priority'] ?? 'medium';

        // TODO: Create actual task in your task system
        // For now, we'll just log it
        Log::info('Task created by workflow', [
            'title' => $title,
            'description' => $description,
            'assignee_id' => $assigneeId,
            'due_date' => $dueDate,
            'priority' => $priority,
            'context' => $context,
        ]);

        return [
            'success' => true,
            'action_type' => 'create_task',
            'details' => [
                'title' => $title,
                'assignee_id' => $assigneeId,
                'due_date' => $dueDate,
                'priority' => $priority,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Assign a resource to a learner.
     */
    public function assignResource(array $config, array $context): array
    {
        $resourceId = $config['resource_id'] ?? null;
        $learnerId = $context['learner_id'] ?? $context['contact_id'] ?? null;

        if (! $resourceId || ! $learnerId) {
            return [
                'success' => false,
                'action_type' => 'assign_resource',
                'error' => 'Missing resource_id or learner_id',
                'executed_at' => now()->toISOString(),
            ];
        }

        // TODO: Implement actual resource assignment
        Log::info('Resource assigned by workflow', [
            'resource_id' => $resourceId,
            'learner_id' => $learnerId,
            'context' => $context,
        ]);

        return [
            'success' => true,
            'action_type' => 'assign_resource',
            'details' => [
                'resource_id' => $resourceId,
                'learner_id' => $learnerId,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Send in-app notification.
     */
    public function sendInAppNotification(array $config, array $context): array
    {
        $recipients = $this->resolveRecipients($config['recipients'] ?? [], $context);
        $title = $this->interpolationService->interpolateTemplate($config['title'] ?? 'Alert', $context);
        $message = $this->interpolationService->interpolateTemplate($config['message'] ?? '', $context);
        $priority = $config['priority'] ?? UserNotification::PRIORITY_NORMAL;
        $url = $this->interpolationService->interpolateTemplate($config['url'] ?? '', $context);
        $actionLabel = $config['action_label'] ?? 'View Details';

        $notificationService = app(NotificationService::class);
        $deliveryService = app(NotificationDeliveryService::class);

        $sent = 0;
        $userIds = [];

        foreach ($recipients as $recipient) {
            $userId = $recipient['id'] ?? $recipient['user_id'] ?? $recipient;

            if ($userId && is_numeric($userId)) {
                $user = User::find($userId);
                if ($user) {
                    $userIds[] = $userId;
                }
            }
        }

        if (empty($userIds)) {
            return [
                'success' => false,
                'action_type' => 'in_app_notification',
                'details' => [
                    'sent' => 0,
                    'total' => count($recipients),
                    'error' => 'No valid recipients found',
                ],
                'executed_at' => now()->toISOString(),
            ];
        }

        // Build notification data
        $notificationData = [
            'title' => $title,
            'body' => $message,
            'priority' => $priority,
            'metadata' => [
                'workflow_context' => array_intersect_key($context, array_flip([
                    'workflow_id',
                    'workflow_name',
                    'execution_id',
                    'trigger_type',
                    'learner_id',
                    'contact_id',
                ])),
            ],
        ];

        if (! empty($url)) {
            $notificationData['action_url'] = $url;
            $notificationData['action_label'] = $actionLabel;
        }

        // Add notifiable reference if we have workflow context
        if (isset($context['execution_id'])) {
            $notificationData['notifiable_type'] = WorkflowExecution::class;
            $notificationData['notifiable_id'] = $context['execution_id'];
        }

        // Create notifications and dispatch multi-channel delivery
        $sent = $notificationService->notifyMany(
            $userIds,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'workflow_custom',
            $notificationData
        );

        // Dispatch multi-channel delivery for created notifications
        if ($sent > 0) {
            $notifications = UserNotification::where('type', 'workflow_custom')
                ->whereIn('user_id', $userIds)
                ->where('created_at', '>=', now()->subMinute())
                ->orderBy('created_at', 'desc')
                ->limit($sent)
                ->get();

            $deliveryService->deliverMany($notifications);
        }

        return [
            'success' => $sent > 0,
            'action_type' => 'in_app_notification',
            'details' => [
                'sent' => $sent,
                'total' => count($recipients),
                'title' => $title,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Trigger another workflow.
     */
    public function triggerWorkflow(array $config, array $context): array
    {
        $workflowId = $config['workflow_id'] ?? null;

        if (! $workflowId) {
            return [
                'success' => false,
                'action_type' => 'trigger_workflow',
                'error' => 'No workflow_id specified',
                'executed_at' => now()->toISOString(),
            ];
        }

        $workflow = Workflow::find($workflowId);

        if (! $workflow || ! $workflow->isActive()) {
            return [
                'success' => false,
                'action_type' => 'trigger_workflow',
                'error' => 'Workflow not found or not active',
                'executed_at' => now()->toISOString(),
            ];
        }

        // Dispatch the sub-workflow (will be handled by ProcessWorkflow job)
        \App\Jobs\ProcessWorkflow::dispatch($workflow, array_merge($context, [
            'triggered_by_workflow' => $context['workflow_id'] ?? null,
        ]));

        return [
            'success' => true,
            'action_type' => 'trigger_workflow',
            'details' => [
                'workflow_id' => $workflowId,
                'workflow_name' => $workflow->name,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Update a field on the triggering entity.
     */
    public function updateField(array $config, array $context): array
    {
        $field = $config['field'] ?? null;
        $value = $config['value'] ?? null;
        $entityType = $config['entity_type'] ?? 'learner';
        $entityId = $context["{$entityType}_id"] ?? $context['contact_id'] ?? null;

        if (! $field || ! $entityId) {
            return [
                'success' => false,
                'action_type' => 'update_field',
                'error' => 'Missing field or entity_id',
                'executed_at' => now()->toISOString(),
            ];
        }

        // TODO: Implement actual field update with proper authorization
        Log::info('Field update requested by workflow', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'field' => $field,
            'value' => $value,
        ]);

        return [
            'success' => true,
            'action_type' => 'update_field',
            'details' => [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'field' => $field,
                'value' => $value,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Handle unknown action type.
     */
    protected function unknownAction(string $actionType): array
    {
        return [
            'success' => false,
            'action_type' => $actionType,
            'error' => "Unknown action type: {$actionType}",
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Resolve recipients from config.
     * Supports: direct values, user IDs, role-based, dynamic from context.
     */
    protected function resolveRecipients(array $recipients, array $context): array
    {
        $resolved = [];

        foreach ($recipients as $recipient) {
            if (is_string($recipient)) {
                // Check if it's a context reference
                if ($this->interpolationService->isContextReference($recipient)) {
                    $key = $this->interpolationService->extractContextKey($recipient);
                    $value = data_get($context, $key);
                    if ($value) {
                        $resolved[] = $value;
                    }
                } else {
                    $resolved[] = $recipient;
                }
            } elseif (is_array($recipient)) {
                if (isset($recipient['type'])) {
                    switch ($recipient['type']) {
                        case 'user':
                            $user = User::find($recipient['id']);
                            if ($user) {
                                $resolved[] = [
                                    'id' => $user->id,
                                    'email' => $user->email,
                                    'phone' => $user->phone,
                                ];
                            }
                            break;

                        case 'role':
                            $users = User::where('org_id', $context['org_id'] ?? null)
                                ->where('role', $recipient['role'])
                                ->get();
                            foreach ($users as $user) {
                                $resolved[] = [
                                    'id' => $user->id,
                                    'email' => $user->email,
                                    'phone' => $user->phone,
                                ];
                            }
                            break;

                        case 'learner_contact':
                            // Get learner's emergency contacts
                            $learnerId = $context['learner_id'] ?? null;
                            if ($learnerId) {
                                $learner = Learner::find($learnerId);
                                if ($learner && $learner->emergency_contacts) {
                                    foreach ($learner->emergency_contacts as $contact) {
                                        $resolved[] = $contact;
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    $resolved[] = $recipient;
                }
            }
        }

        return $resolved;
    }
}
