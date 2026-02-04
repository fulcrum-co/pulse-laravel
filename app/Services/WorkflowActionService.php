<?php

namespace App\Services;

use App\Models\ContactList;
use App\Models\MiniCourse;
use App\Models\Student;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class WorkflowActionService
{
    public function __construct(
        protected SinchService $sinchService
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
                'generate_course' => $this->generateCourse($config, $context),
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
        $subject = $this->interpolateTemplate($config['subject'] ?? 'Alert Notification', $context);
        $body = $this->interpolateTemplate($config['body'] ?? '', $context);
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
        $message = $this->interpolateTemplate($config['message'] ?? 'Alert from Pulse', $context);

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
        $message = $this->interpolateTemplate($config['message'] ?? 'Alert from Pulse', $context);

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
        $message = $this->interpolateTemplate($config['message'] ?? 'This is an automated alert from Pulse.', $context);

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
        $url = $this->interpolateTemplate($url, $context);
        if (is_array($payload)) {
            $payload = $this->interpolateArrayValues($payload, $context);
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
        $title = $this->interpolateTemplate($config['title'] ?? 'Follow-up Task', $context);
        $description = $this->interpolateTemplate($config['description'] ?? '', $context);
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
     * Assign a resource to a student.
     */
    public function assignResource(array $config, array $context): array
    {
        $resourceId = $config['resource_id'] ?? null;
        $studentId = $context['student_id'] ?? $context['contact_id'] ?? null;

        if (! $resourceId || ! $studentId) {
            return [
                'success' => false,
                'action_type' => 'assign_resource',
                'error' => 'Missing resource_id or student_id',
                'executed_at' => now()->toISOString(),
            ];
        }

        // TODO: Implement actual resource assignment
        Log::info('Resource assigned by workflow', [
            'resource_id' => $resourceId,
            'student_id' => $studentId,
            'context' => $context,
        ]);

        return [
            'success' => true,
            'action_type' => 'assign_resource',
            'details' => [
                'resource_id' => $resourceId,
                'student_id' => $studentId,
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
        $title = $this->interpolateTemplate($config['title'] ?? 'Alert', $context);
        $message = $this->interpolateTemplate($config['message'] ?? '', $context);
        $priority = $config['priority'] ?? UserNotification::PRIORITY_NORMAL;
        $url = $this->interpolateTemplate($config['url'] ?? '', $context);
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
                    'student_id',
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
        $entityType = $config['entity_type'] ?? 'student';
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
     * Generate an AI course for target student(s) or group.
     *
     * Config options:
     * - target_type: student|contact_list|group (default: student)
     * - target_ids: array of IDs (optional, uses context if not provided)
     * - topic: custom topic (optional, auto-detected from student signals if null)
     * - course_type: intervention|skill_building|wellness etc.
     * - duration_minutes: 15|30|45|60
     * - auto_enroll: bool - automatically enroll target students
     * - notify_targets: bool - send notification when course is ready
     */
    public function generateCourse(array $config, array $context): array
    {
        $targetType = $config['target_type'] ?? 'student';
        $targetIds = $config['target_ids'] ?? [];
        $topic = $config['topic'] ?? null;
        $courseType = $config['course_type'] ?? MiniCourse::TYPE_INTERVENTION;
        $durationMinutes = $config['duration_minutes'] ?? 30;
        $autoEnroll = $config['auto_enroll'] ?? true;
        $notifyTargets = $config['notify_targets'] ?? true;

        $orgId = $context['org_id'] ?? null;
        $workflowId = $context['workflow_id'] ?? null;

        // Resolve target IDs from context if not explicitly provided
        if (empty($targetIds)) {
            if ($targetType === 'student' && isset($context['student_id'])) {
                $targetIds = [$context['student_id']];
            } elseif ($targetType === 'contact_list' && isset($context['contact_list_id'])) {
                $targetIds = [$context['contact_list_id']];
            }
        }

        if (empty($targetIds) || ! $orgId) {
            return [
                'success' => false,
                'action_type' => 'generate_course',
                'error' => 'Missing target IDs or org_id',
                'executed_at' => now()->toISOString(),
            ];
        }

        $orchestrator = app(CourseOrchestrator::class);
        $generated = [];
        $errors = [];

        foreach ($targetIds as $targetId) {
            try {
                $courseParams = $this->buildCourseParamsForTarget(
                    $targetType,
                    $targetId,
                    $topic,
                    $courseType,
                    $durationMinutes,
                    $orgId,
                    $workflowId
                );

                if (! $courseParams) {
                    $errors[] = [
                        'target_id' => $targetId,
                        'error' => 'Could not resolve target or determine topic',
                    ];

                    continue;
                }

                // Generate the course with workflow trigger source
                $course = $orchestrator->generateCourse($courseParams);

                // Update trigger source to workflow (ensures moderation required)
                $course->update([
                    'generation_trigger' => MiniCourse::TRIGGER_WORKFLOW,
                    'approval_status' => MiniCourse::APPROVAL_PENDING,
                    'workflow_context' => [
                        'workflow_id' => $workflowId,
                        'execution_id' => $context['execution_id'] ?? null,
                        'trigger_type' => $context['trigger_type'] ?? 'workflow',
                        'target_type' => $targetType,
                        'target_id' => $targetId,
                    ],
                ]);

                // Auto-enroll students if enabled
                if ($autoEnroll && $targetType === 'student') {
                    $this->enrollStudentInCourse($targetId, $course);
                } elseif ($autoEnroll && $targetType === 'contact_list') {
                    $this->enrollContactListInCourse($targetId, $course);
                }

                $generated[] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'target_id' => $targetId,
                    'target_type' => $targetType,
                ];

                Log::info('Workflow generated course', [
                    'course_id' => $course->id,
                    'workflow_id' => $workflowId,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                ]);
            } catch (\Exception $e) {
                $errors[] = [
                    'target_id' => $targetId,
                    'error' => $e->getMessage(),
                ];

                Log::error('Workflow course generation failed', [
                    'target_id' => $targetId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => count($generated) > 0,
            'action_type' => 'generate_course',
            'details' => [
                'generated_count' => count($generated),
                'error_count' => count($errors),
                'generated' => $generated,
                'errors' => $errors,
            ],
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Build course generation parameters based on target type.
     */
    protected function buildCourseParamsForTarget(
        string $targetType,
        int $targetId,
        ?string $topic,
        string $courseType,
        int $durationMinutes,
        int $orgId,
        ?int $workflowId
    ): ?array {
        $targetGrades = [];
        $targetRiskLevels = [];

        // Resolve topic and context based on target type
        if ($targetType === 'student') {
            $student = Student::find($targetId);
            if (! $student) {
                return null;
            }

            // Auto-detect topic from student signals if not provided
            if (! $topic) {
                $topic = $this->inferTopicFromStudentSignals($student);
            }

            $targetGrades = [$student->grade_level];
            $targetRiskLevels = [$student->risk_level ?? 'moderate'];
        } elseif ($targetType === 'contact_list') {
            $contactList = ContactList::find($targetId);
            if (! $contactList) {
                return null;
            }

            // Use contact list name as topic hint if not provided
            if (! $topic) {
                $topic = 'Support course for '.$contactList->name;
            }
        }

        // Fallback topic
        if (! $topic) {
            $topic = 'Personalized support and skill-building';
        }

        return [
            'topic' => $topic,
            'orgId' => $orgId,
            'targetGrades' => array_filter($targetGrades),
            'targetRiskLevels' => array_filter($targetRiskLevels),
            'targetDurationMinutes' => $durationMinutes,
            'courseType' => $courseType,
            'createdBy' => null, // System-generated
            'triggerSource' => 'workflow',
            'workflowId' => $workflowId,
        ];
    }

    /**
     * Infer a relevant course topic from student's risk signals.
     */
    protected function inferTopicFromStudentSignals(Student $student): string
    {
        // Check for domain-specific risk indicators
        $domainScores = $student->domain_risk_scores ?? [];

        // Find the highest risk domain
        $highestDomain = null;
        $highestScore = 0;

        foreach ($domainScores as $domain => $score) {
            if ($score > $highestScore) {
                $highestScore = $score;
                $highestDomain = $domain;
            }
        }

        // Map domains to relevant course topics
        $topicMap = [
            'anxiety' => 'Managing anxiety and building coping skills',
            'depression' => 'Building emotional resilience and positive mindset',
            'stress' => 'Stress management and self-care strategies',
            'social' => 'Building healthy relationships and social skills',
            'academic' => 'Study skills and academic success strategies',
            'behavioral' => 'Self-regulation and positive behavior strategies',
            'attendance' => 'Motivation and engagement in learning',
            'family' => 'Navigating family challenges and building support',
        ];

        if ($highestDomain && isset($topicMap[$highestDomain])) {
            return $topicMap[$highestDomain];
        }

        // Fall back to general risk level-based topic
        $riskLevel = $student->risk_level ?? 'moderate';

        return match ($riskLevel) {
            'high', 'crisis' => 'Building coping skills and getting support',
            'moderate' => 'Skill-building for personal growth',
            default => 'Wellness and personal development',
        };
    }

    /**
     * Enroll a student in the generated course.
     */
    protected function enrollStudentInCourse(int $studentId, MiniCourse $course): void
    {
        // Check if enrollment model/system exists
        if (class_exists(\App\Models\MiniCourseEnrollment::class)) {
            \App\Models\MiniCourseEnrollment::firstOrCreate([
                'mini_course_id' => $course->id,
                'student_id' => $studentId,
            ], [
                'enrolled_at' => now(),
                'status' => 'enrolled',
                'enrolled_by' => null, // System enrollment
            ]);
        }
    }

    /**
     * Enroll all students in a contact list in the course.
     */
    protected function enrollContactListInCourse(int $contactListId, MiniCourse $course): void
    {
        $contactList = ContactList::with('contacts')->find($contactListId);
        if (! $contactList) {
            return;
        }

        foreach ($contactList->contacts as $contact) {
            if ($contact->contact_type === 'student') {
                $this->enrollStudentInCourse($contact->contact_id, $course);
            }
        }
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
                if (str_starts_with($recipient, '{{') && str_ends_with($recipient, '}}')) {
                    $key = trim($recipient, '{} ');
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

                        case 'student_contact':
                            // Get student's emergency contacts
                            $studentId = $context['student_id'] ?? null;
                            if ($studentId) {
                                $student = Student::find($studentId);
                                if ($student && $student->emergency_contacts) {
                                    foreach ($student->emergency_contacts as $contact) {
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

    /**
     * Interpolate template variables with context values.
     */
    protected function interpolateTemplate(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($context) {
            $key = trim($matches[1]);

            return data_get($context, $key, $matches[0]);
        }, $template);
    }

    /**
     * Interpolate array values recursively.
     */
    protected function interpolateArrayValues(array $array, array $context): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $this->interpolateTemplate($value, $context);
            } elseif (is_array($value)) {
                $result[$key] = $this->interpolateArrayValues($value, $context);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
