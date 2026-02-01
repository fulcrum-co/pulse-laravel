<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationPreferences extends Component
{
    /**
     * Notification preferences organized by category.
     */
    public array $preferences = [];

    /**
     * Priority-based channel preferences.
     */
    public array $channelsByPriority = [];

    /**
     * Quiet hours settings.
     */
    public bool $quietHoursEnabled = false;

    public string $quietHoursStart = '21:00';

    public string $quietHoursEnd = '07:00';

    public ?string $quietHoursTimezone = null;

    /**
     * Digest settings.
     */
    public bool $digestEnabled = true;

    public string $digestFrequency = 'daily';

    public string $digestDay = 'monday';

    public string $digestTime = '07:00';

    public bool $digestSuppressIndividual = false;

    /**
     * Toast settings.
     */
    public bool $toastEnabled = true;

    public string $toastPriorityThreshold = 'low';

    /**
     * Type overrides (for expandable category sections).
     */
    public array $typeOverrides = [];

    /**
     * Expanded category sections.
     */
    public array $expandedCategories = [];

    /**
     * Category labels for display.
     */
    protected array $categoryLabels = [
        'workflow' => 'Alert Workflows',
        'workflow_custom' => 'Custom Workflow Alerts',
        'survey' => 'Surveys',
        'report' => 'Reports',
        'strategy' => 'Plans',
        'course' => 'Courses',
        'collection' => 'Data Collection',
        'system' => 'System Announcements',
    ];

    /**
     * Category descriptions for display.
     */
    protected array $categoryDescriptions = [
        'workflow' => 'Notifications when your alert workflows complete or fail',
        'workflow_custom' => 'Custom notifications triggered by workflow actions',
        'survey' => 'Survey assignments, reminders, and completion notices',
        'report' => 'Report publishing and assignment notifications',
        'strategy' => 'Plan updates and deadline reminders',
        'course' => 'Course approvals and completion notifications',
        'collection' => 'Data collection reminders and session notices',
        'system' => 'Important system announcements from administrators',
    ];

    /**
     * Notification types by category.
     */
    protected array $typesByCategory = [
        'workflow' => [
            'workflow_triggered' => 'Workflow triggered',
            'workflow_completed' => 'Workflow completed',
            'workflow_failed' => 'Workflow failed',
        ],
        'workflow_custom' => [
            'workflow_action_needed' => 'Action required',
        ],
        'survey' => [
            'survey_assigned' => 'Survey assigned',
            'survey_closing_soon' => 'Survey closing soon',
            'survey_completed' => 'Survey completed',
        ],
        'report' => [
            'report_published' => 'Report published',
            'report_shared' => 'Report shared',
        ],
        'strategy' => [
            'activity_due_soon' => 'Activity due soon',
            'activity_status_changed' => 'Activity status changed',
            'objective_at_risk' => 'Objective at risk',
        ],
        'course' => [
            'course_approval_needed' => 'Course approval needed',
            'course_approved' => 'Course approved',
            'course_enrolled' => 'Course enrollment',
        ],
        'collection' => [
            'collection_reminder' => 'Collection reminder',
            'collection_overdue' => 'Collection overdue',
        ],
        'system' => [
            'admin_announcement' => 'Admin announcement',
            'welcome' => 'Welcome message',
        ],
    ];

    /**
     * Priority labels for display.
     */
    protected array $priorityLabels = [
        'urgent' => 'Critical',
        'high' => 'High',
        'normal' => 'Normal',
        'low' => 'Low',
    ];

    /**
     * Channel labels for display.
     */
    protected array $channelLabels = [
        'in_app' => 'In-App',
        'email' => 'Email',
        'sms' => 'SMS',
    ];

    /**
     * Common timezones for selection.
     */
    protected array $timezones = [
        'America/New_York' => 'Eastern Time (ET)',
        'America/Chicago' => 'Central Time (CT)',
        'America/Denver' => 'Mountain Time (MT)',
        'America/Los_Angeles' => 'Pacific Time (PT)',
        'America/Anchorage' => 'Alaska Time (AKT)',
        'Pacific/Honolulu' => 'Hawaii Time (HT)',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $prefs = $user->notification_preferences;

        // Load category preferences
        foreach (array_keys($this->categoryLabels) as $category) {
            $this->preferences[$category] = [
                'in_app' => $prefs[$category]['in_app'] ?? true,
                'email' => $prefs[$category]['email'] ?? false,
                'sms' => $prefs[$category]['sms'] ?? false,
            ];
        }

        // Load priority-based channel preferences
        $channels = $prefs['channels'] ?? [];
        foreach (['urgent', 'high', 'normal', 'low'] as $priority) {
            $this->channelsByPriority[$priority] = [
                'in_app' => true, // Always true
                'email' => $channels[$priority]['email'] ?? ($priority !== 'low'),
                'sms' => $channels[$priority]['sms'] ?? ($priority === 'urgent'),
            ];
        }

        // Load quiet hours settings
        $quietHours = $prefs['quiet_hours'] ?? [];
        $this->quietHoursEnabled = $quietHours['enabled'] ?? false;
        $this->quietHoursStart = $quietHours['start'] ?? '21:00';
        $this->quietHoursEnd = $quietHours['end'] ?? '07:00';
        $this->quietHoursTimezone = $quietHours['timezone'] ?? null;

        // Load digest settings
        $digest = $prefs['digest'] ?? [];
        $this->digestEnabled = $digest['enabled'] ?? true;
        $this->digestFrequency = $digest['frequency'] ?? 'daily';
        $this->digestDay = $digest['day'] ?? 'monday';
        $this->digestTime = $digest['time'] ?? '07:00';
        $this->digestSuppressIndividual = $digest['suppress_individual_emails'] ?? false;

        // Load toast settings
        $toast = $prefs['toast'] ?? [];
        $this->toastEnabled = $toast['enabled'] ?? true;
        $this->toastPriorityThreshold = $toast['priority_threshold'] ?? 'low';

        // Load type overrides
        $this->typeOverrides = $prefs['type_overrides'] ?? [];
    }

    /**
     * Toggle a specific category preference.
     */
    public function togglePreference(string $category, string $channel): void
    {
        // In-app notifications cannot be disabled
        if ($channel === 'in_app') {
            return;
        }

        $this->preferences[$category][$channel] = ! $this->preferences[$category][$channel];
        $this->savePreferences();
    }

    /**
     * Toggle a priority-based channel preference.
     */
    public function togglePriorityChannel(string $priority, string $channel): void
    {
        // In-app cannot be disabled
        if ($channel === 'in_app') {
            return;
        }

        // Critical/urgent admin notifications cannot be disabled
        if ($priority === 'urgent') {
            return;
        }

        $this->channelsByPriority[$priority][$channel] = ! $this->channelsByPriority[$priority][$channel];
        $this->savePreferences();
    }

    /**
     * Toggle a type override.
     */
    public function toggleTypeOverride(string $type): void
    {
        if (isset($this->typeOverrides[$type])) {
            // If currently disabled, remove override (enable)
            if ($this->typeOverrides[$type] === false) {
                unset($this->typeOverrides[$type]);
            } else {
                // Disable
                $this->typeOverrides[$type] = false;
            }
        } else {
            // First toggle disables it
            $this->typeOverrides[$type] = false;
        }
        $this->savePreferences();
    }

    /**
     * Toggle a category expansion.
     */
    public function toggleCategoryExpansion(string $category): void
    {
        if (in_array($category, $this->expandedCategories)) {
            $this->expandedCategories = array_values(array_diff($this->expandedCategories, [$category]));
        } else {
            $this->expandedCategories[] = $category;
        }
    }

    /**
     * Toggle quiet hours.
     */
    public function toggleQuietHours(): void
    {
        $this->quietHoursEnabled = ! $this->quietHoursEnabled;
        $this->savePreferences();
    }

    /**
     * Toggle digest.
     */
    public function toggleDigest(): void
    {
        $this->digestEnabled = ! $this->digestEnabled;
        $this->savePreferences();
    }

    /**
     * Toggle digest suppress individual emails.
     */
    public function toggleDigestSuppressIndividual(): void
    {
        $this->digestSuppressIndividual = ! $this->digestSuppressIndividual;
        $this->savePreferences();
    }

    /**
     * Toggle toast.
     */
    public function toggleToast(): void
    {
        $this->toastEnabled = ! $this->toastEnabled;
        $this->savePreferences();
    }

    /**
     * Update quiet hours times.
     */
    public function updatedQuietHoursStart(): void
    {
        $this->savePreferences();
    }

    public function updatedQuietHoursEnd(): void
    {
        $this->savePreferences();
    }

    public function updatedQuietHoursTimezone(): void
    {
        $this->savePreferences();
    }

    /**
     * Update digest settings.
     */
    public function updatedDigestFrequency(): void
    {
        $this->savePreferences();
    }

    public function updatedDigestDay(): void
    {
        $this->savePreferences();
    }

    public function updatedDigestTime(): void
    {
        $this->savePreferences();
    }

    /**
     * Update toast threshold.
     */
    public function updatedToastPriorityThreshold(): void
    {
        $this->savePreferences();
    }

    /**
     * Save all preferences to the database.
     */
    protected function savePreferences(): void
    {
        $user = Auth::user();

        $prefsToSave = $this->preferences;

        // Add priority-based channels
        $prefsToSave['channels'] = $this->channelsByPriority;

        // Add type overrides
        $prefsToSave['type_overrides'] = $this->typeOverrides;

        // Add quiet hours
        $prefsToSave['quiet_hours'] = [
            'enabled' => $this->quietHoursEnabled,
            'start' => $this->quietHoursStart,
            'end' => $this->quietHoursEnd,
            'timezone' => $this->quietHoursTimezone,
        ];

        // Add digest settings
        $prefsToSave['digest'] = [
            'enabled' => $this->digestEnabled,
            'frequency' => $this->digestFrequency,
            'day' => $this->digestDay,
            'time' => $this->digestTime,
            'suppress_individual_emails' => $this->digestSuppressIndividual,
        ];

        // Add toast settings
        $prefsToSave['toast'] = [
            'enabled' => $this->toastEnabled,
            'priority_threshold' => $this->toastPriorityThreshold,
        ];

        $user->updateNotificationPreferences($prefsToSave);

        $this->dispatch('preferences-saved');
    }

    /**
     * Enable all notifications for a category.
     */
    public function enableAll(string $category): void
    {
        $this->preferences[$category] = [
            'in_app' => true,
            'email' => true,
            'sms' => true,
        ];
        $this->savePreferences();
    }

    /**
     * Disable optional notifications for a category (keep in-app).
     */
    public function disableOptional(string $category): void
    {
        $this->preferences[$category] = [
            'in_app' => true,
            'email' => false,
            'sms' => false,
        ];
        $this->savePreferences();
    }

    /**
     * Reset all preferences to defaults.
     */
    public function resetToDefaults(): void
    {
        $defaults = User::DEFAULT_NOTIFICATION_PREFERENCES;

        // Reset category preferences
        foreach (array_keys($this->categoryLabels) as $category) {
            $this->preferences[$category] = $defaults[$category] ?? [
                'in_app' => true,
                'email' => false,
                'sms' => false,
            ];
        }

        // Reset priority channels
        $channels = $defaults['channels'] ?? [];
        foreach (['urgent', 'high', 'normal', 'low'] as $priority) {
            $this->channelsByPriority[$priority] = $channels[$priority] ?? [
                'in_app' => true,
                'email' => $priority !== 'low',
                'sms' => $priority === 'urgent',
            ];
        }

        // Reset type overrides
        $this->typeOverrides = [];

        // Reset quiet hours
        $quietHours = $defaults['quiet_hours'] ?? [];
        $this->quietHoursEnabled = $quietHours['enabled'] ?? false;
        $this->quietHoursStart = $quietHours['start'] ?? '21:00';
        $this->quietHoursEnd = $quietHours['end'] ?? '07:00';
        $this->quietHoursTimezone = $quietHours['timezone'] ?? null;

        // Reset digest
        $digest = $defaults['digest'] ?? [];
        $this->digestEnabled = $digest['enabled'] ?? true;
        $this->digestFrequency = $digest['frequency'] ?? 'daily';
        $this->digestDay = $digest['day'] ?? 'monday';
        $this->digestTime = $digest['time'] ?? '07:00';
        $this->digestSuppressIndividual = $digest['suppress_individual_emails'] ?? false;

        // Reset toast
        $toast = $defaults['toast'] ?? [];
        $this->toastEnabled = $toast['enabled'] ?? true;
        $this->toastPriorityThreshold = $toast['priority_threshold'] ?? 'low';

        $this->savePreferences();
    }

    public function render()
    {
        return view('livewire.settings.notification-preferences', [
            'categoryLabels' => $this->categoryLabels,
            'categoryDescriptions' => $this->categoryDescriptions,
            'channelLabels' => $this->channelLabels,
            'priorityLabels' => $this->priorityLabels,
            'typesByCategory' => $this->typesByCategory,
            'timezones' => $this->timezones,
        ]);
    }
}
