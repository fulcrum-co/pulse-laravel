<?php

namespace App\Livewire\Admin;

use App\Models\CourseApprovalWorkflow;
use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\OrganizationSettings;
use App\Services\CourseApprovalService;
use Livewire\Component;

class AICourseSettings extends Component
{
    // Settings fields
    public string $approvalMode = 'create_approve';

    public bool $autoGenerateEnabled = false;

    public array $generationTriggers = [];

    public array $notificationRecipients = [];

    public int $maxAutoCoursesPerDay = 10;

    public bool $requireReviewForAiGenerated = true;

    // Scheduling settings
    public string $schedule = 'disabled';

    public string $scheduleTime = '06:00';

    public string $scheduleDay = 'monday';

    public int $scheduleDate = 1;

    // Target criteria
    public array $targetRiskLevels = ['high', 'moderate'];

    public array $targetGrades = [];

    public bool $missingCoursesOnly = true;

    public string $defaultCourseType = 'intervention';

    public int $defaultDurationMinutes = 30;

    public bool $autoEnroll = true;

    public bool $notifyOnGeneration = true;

    // Pending approvals
    public bool $showApprovalModal = false;

    public ?int $selectedWorkflowId = null;

    public string $reviewNotes = '';

    public string $rejectionReason = '';

    protected CourseApprovalService $approvalService;

    public function boot(CourseApprovalService $approvalService): void
    {
        $this->approvalService = $approvalService;
    }

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $user = auth()->user();
        $org = Organization::find($user->org_id);

        if ($org) {
            // Load legacy settings
            $legacySettings = $org->settings['ai_course_settings'] ?? [];
            $this->approvalMode = $legacySettings['approval_mode'] ?? 'create_approve';
            $this->generationTriggers = $legacySettings['generation_triggers'] ?? [];
            $this->notificationRecipients = $legacySettings['notification_recipients'] ?? [];

            // Load auto-course generation settings from OrganizationSettings
            $orgSettings = OrganizationSettings::forOrganization($user->org_id);
            $autoSettings = $orgSettings->getAutoCourseSettings();

            $this->autoGenerateEnabled = $autoSettings['enabled'];
            $this->schedule = $autoSettings['schedule'];
            $this->scheduleTime = $autoSettings['schedule_time'];
            $this->scheduleDay = $autoSettings['schedule_day'];
            $this->scheduleDate = $autoSettings['schedule_date'];
            $this->maxAutoCoursesPerDay = $autoSettings['max_courses_per_day'];
            $this->targetRiskLevels = $autoSettings['target_criteria']['risk_levels'] ?? ['high', 'moderate'];
            $this->targetGrades = $autoSettings['target_criteria']['grades'] ?? [];
            $this->missingCoursesOnly = $autoSettings['target_criteria']['missing_courses_only'] ?? true;
            $this->defaultCourseType = $autoSettings['default_course_type'];
            $this->defaultDurationMinutes = $autoSettings['default_duration_minutes'];
            $this->requireReviewForAiGenerated = $autoSettings['require_moderation'];
            $this->autoEnroll = $autoSettings['auto_enroll'];
            $this->notifyOnGeneration = $autoSettings['notify_on_generation'];
        }
    }

    public function saveSettings(): void
    {
        $this->validate([
            'approvalMode' => 'required|in:auto_activate,create_approve,approve_first',
            'maxAutoCoursesPerDay' => 'required|integer|min:1|max:100',
            'schedule' => 'required|in:disabled,daily,weekly,monthly',
            'scheduleTime' => 'required|date_format:H:i',
            'scheduleDay' => 'required_if:schedule,weekly',
            'scheduleDate' => 'required_if:schedule,monthly|integer|min:1|max:28',
            'defaultDurationMinutes' => 'required|integer|in:15,30,45,60',
        ]);

        $user = auth()->user();
        $org = Organization::find($user->org_id);

        if ($org) {
            // Save legacy settings to Organization
            $settings = $org->settings ?? [];
            $settings['ai_course_settings'] = [
                'approval_mode' => $this->approvalMode,
                'generation_triggers' => $this->generationTriggers,
                'notification_recipients' => $this->notificationRecipients,
            ];
            $org->update(['settings' => $settings]);

            // Save auto-course generation settings to OrganizationSettings
            $orgSettings = OrganizationSettings::forOrganization($user->org_id);
            $orgSettings->setAutoCourseSettings([
                'enabled' => $this->autoGenerateEnabled,
                'schedule' => $this->schedule,
                'schedule_time' => $this->scheduleTime,
                'schedule_day' => $this->scheduleDay,
                'schedule_date' => $this->scheduleDate,
                'max_courses_per_day' => $this->maxAutoCoursesPerDay,
                'target_criteria' => [
                    'risk_levels' => $this->targetRiskLevels,
                    'grades' => $this->targetGrades,
                    'missing_courses_only' => $this->missingCoursesOnly,
                ],
                'default_course_type' => $this->defaultCourseType,
                'default_duration_minutes' => $this->defaultDurationMinutes,
                'require_moderation' => $this->requireReviewForAiGenerated,
                'auto_enroll' => $this->autoEnroll,
                'notify_on_generation' => $this->notifyOnGeneration,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'AI course settings saved successfully.',
            ]);
        }
    }

    public function toggleTrigger(string $trigger): void
    {
        if (in_array($trigger, $this->generationTriggers)) {
            $this->generationTriggers = array_values(array_diff($this->generationTriggers, [$trigger]));
        } else {
            $this->generationTriggers[] = $trigger;
        }
    }

    public function toggleRecipient(string $recipient): void
    {
        if (in_array($recipient, $this->notificationRecipients)) {
            $this->notificationRecipients = array_values(array_diff($this->notificationRecipients, [$recipient]));
        } else {
            $this->notificationRecipients[] = $recipient;
        }
    }

    public function toggleRiskLevel(string $level): void
    {
        if (in_array($level, $this->targetRiskLevels)) {
            $this->targetRiskLevels = array_values(array_diff($this->targetRiskLevels, [$level]));
        } else {
            $this->targetRiskLevels[] = $level;
        }
    }

    public function toggleGrade(string $grade): void
    {
        if (in_array($grade, $this->targetGrades)) {
            $this->targetGrades = array_values(array_diff($this->targetGrades, [$grade]));
        } else {
            $this->targetGrades[] = $grade;
        }
    }

    public function getRiskLevelOptionsProperty(): array
    {
        return [
            'low' => 'Low Risk',
            'moderate' => 'Moderate Risk',
            'high' => 'High Risk',
            'crisis' => 'Crisis',
        ];
    }

    public function getGradeOptionsProperty(): array
    {
        return [
            'K' => 'Kindergarten',
            '1' => 'Grade 1',
            '2' => 'Grade 2',
            '3' => 'Grade 3',
            '4' => 'Grade 4',
            '5' => 'Grade 5',
            '6' => 'Grade 6',
            '7' => 'Grade 7',
            '8' => 'Grade 8',
            '9' => 'Grade 9',
            '10' => 'Grade 10',
            '11' => 'Grade 11',
            '12' => 'Grade 12',
        ];
    }

    public function getScheduleOptionsProperty(): array
    {
        return [
            'disabled' => 'Disabled',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    public function getDayOptionsProperty(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }

    public function getDurationOptionsProperty(): array
    {
        return [
            15 => '15 minutes',
            30 => '30 minutes',
            45 => '45 minutes',
            60 => '60 minutes',
        ];
    }

    // ============================================
    // APPROVAL MANAGEMENT
    // ============================================

    public function getPendingApprovalsProperty()
    {
        $user = auth()->user();

        return $this->approvalService->getPendingApprovals($user->org_id);
    }

    public function getApprovalStatsProperty(): array
    {
        $user = auth()->user();
        $orgId = $user->org_id;

        return [
            'pending' => CourseApprovalWorkflow::pending()
                ->whereHas('course', fn ($q) => $q->where('org_id', $orgId))
                ->count(),
            'approved_this_week' => CourseApprovalWorkflow::approved()
                ->whereHas('course', fn ($q) => $q->where('org_id', $orgId))
                ->where('reviewed_at', '>=', now()->startOfWeek())
                ->count(),
            'rejected_this_week' => CourseApprovalWorkflow::rejected()
                ->whereHas('course', fn ($q) => $q->where('org_id', $orgId))
                ->where('reviewed_at', '>=', now()->startOfWeek())
                ->count(),
            'auto_generated_total' => MiniCourse::where('org_id', $orgId)
                ->autoGenerated()
                ->count(),
        ];
    }

    public function openApprovalModal(int $workflowId): void
    {
        $this->selectedWorkflowId = $workflowId;
        $this->reviewNotes = '';
        $this->rejectionReason = '';
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->selectedWorkflowId = null;
        $this->reviewNotes = '';
        $this->rejectionReason = '';
    }

    public function approveCourse(): void
    {
        if (! $this->selectedWorkflowId) {
            return;
        }

        $workflow = CourseApprovalWorkflow::find($this->selectedWorkflowId);

        if ($workflow) {
            $this->approvalService->approve($workflow, auth()->id(), $this->reviewNotes ?: null);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course approved and published.',
            ]);
        }

        $this->closeApprovalModal();
    }

    public function rejectCourse(): void
    {
        if (! $this->selectedWorkflowId) {
            return;
        }

        $this->validate([
            'rejectionReason' => 'required|string|min:10',
        ]);

        $workflow = CourseApprovalWorkflow::find($this->selectedWorkflowId);

        if ($workflow) {
            $this->approvalService->reject($workflow, auth()->id(), $this->rejectionReason);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Course rejected.',
            ]);
        }

        $this->closeApprovalModal();
    }

    public function requestRevision(): void
    {
        if (! $this->selectedWorkflowId) {
            return;
        }

        $this->validate([
            'reviewNotes' => 'required|string|min:10',
        ]);

        $workflow = CourseApprovalWorkflow::find($this->selectedWorkflowId);

        if ($workflow) {
            $this->approvalService->requestRevision($workflow, auth()->id(), $this->reviewNotes);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Revision requested.',
            ]);
        }

        $this->closeApprovalModal();
    }

    public function quickApprove(int $workflowId): void
    {
        $workflow = CourseApprovalWorkflow::find($workflowId);

        if ($workflow) {
            $this->approvalService->approve($workflow, auth()->id());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course approved.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.ai-course-settings', [
            'pendingApprovals' => $this->pendingApprovals,
            'approvalStats' => $this->approvalStats,
            'approvalModes' => CourseApprovalWorkflow::getWorkflowModes(),
            'triggerOptions' => MiniCourse::getGenerationTriggers(),
            'courseTypes' => MiniCourse::getCourseTypes(),
            'riskLevelOptions' => $this->riskLevelOptions,
            'gradeOptions' => $this->gradeOptions,
            'scheduleOptions' => $this->scheduleOptions,
            'dayOptions' => $this->dayOptions,
            'durationOptions' => $this->durationOptions,
        ])->layout('layouts.dashboard', ['title' => 'AI Course Settings']);
    }
}
