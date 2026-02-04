<?php

namespace App\Models;

use App\Services\TerminologyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSettings extends Model
{
    protected $table = 'organization_settings';

    protected $fillable = [
        'org_id',
        'status_labels',
        'risk_labels',
        'settings',
        'contact_label_singular',
        'contact_label_plural',
        'plan_label',
        'primary_color',
        'logo_path',
    ];

    protected $casts = [
        'status_labels' => 'array',
        'risk_labels' => 'array',
        'settings' => 'array',
    ];

    /**
     * Default status labels.
     */
    public const DEFAULT_STATUS_LABELS = [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'off_track' => 'Off Track',
        'not_started' => 'Not Started',
    ];

    /**
     * Default risk labels.
     */
    public const DEFAULT_RISK_LABELS = [
        'good' => 'Good',
        'low' => 'Low Risk',
        'high' => 'High Risk',
    ];

    /**
     * Default terminology - delegates to TerminologyService.
     */
    public const DEFAULT_TERMINOLOGY = TerminologyService::DEFAULTS;

    /**
     * Terminology categories for the admin UI.
     */
    public static function getTerminologyCategories(): array
    {
        return TerminologyService::CATEGORIES;
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get a status label (custom or default).
     */
    public function getStatusLabel(string $key): string
    {
        $labels = $this->status_labels ?? [];

        return $labels[$key] ?? self::DEFAULT_STATUS_LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get a risk label (custom or default).
     */
    public function getRiskLabel(string $key): string
    {
        $labels = $this->risk_labels ?? [];

        return $labels[$key] ?? self::DEFAULT_RISK_LABELS[$key] ?? ucfirst($key);
    }

    /**
     * Get all status labels (merged with defaults).
     */
    public function getAllStatusLabels(): array
    {
        return array_merge(self::DEFAULT_STATUS_LABELS, $this->status_labels ?? []);
    }

    /**
     * Get all risk labels (merged with defaults).
     */
    public function getAllRiskLabels(): array
    {
        return array_merge(self::DEFAULT_RISK_LABELS, $this->risk_labels ?? []);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get or create settings for an organization.
     */
    public static function forOrganization(int $orgId): self
    {
        return self::firstOrCreate(['org_id' => $orgId]);
    }

    /**
     * Get all terminology (custom merged with defaults).
     */
    public function getAllTerminology(): array
    {
        return array_merge(self::DEFAULT_TERMINOLOGY, $this->getTerminology());
    }

    /**
     * Get custom terminology from settings.
     */
    public function getTerminology(): array
    {
        return $this->getSetting('terminology', []);
    }

    /**
     * Set custom terminology in settings.
     */
    public function setTerminologyAttribute(array $terminology): void
    {
        $this->setSetting('terminology', $terminology);
    }

    /**
     * Get terminology attribute (accessor for $settings->terminology).
     */
    public function getTerminologyAttribute(): array
    {
        return $this->getTerminology();
    }

    /**
     * Reset terminology to defaults (clear custom values).
     */
    public function resetTerminology(): void
    {
        $settings = $this->settings ?? [];
        unset($settings['terminology']);
        $this->settings = $settings;
        $this->save();
    }

    public function getContactLabelSingularAttribute(?string $value): string
    {
        return $value ?: 'Contact';
    }

    public function getContactLabelPluralAttribute(?string $value): string
    {
        return $value ?: 'Contacts';
    }

    public function getPlanLabelAttribute(?string $value): string
    {
        return $value ?: 'Plan';
    }

    public function getPrimaryColorAttribute(?string $value): string
    {
        return $value ?: '#3B82F6';
    }

    /**
     * Default auto-course generation settings.
     */
    public const DEFAULT_AUTO_COURSE_SETTINGS = [
        'enabled' => false,
        'schedule' => 'disabled',        // disabled, daily, weekly, monthly
        'schedule_time' => '06:00',      // Time to run (24h format)
        'schedule_day' => 'monday',      // For weekly schedule
        'schedule_date' => 1,            // For monthly (day of month)
        'max_courses_per_day' => 50,     // Limit per org
        'target_criteria' => [
            'risk_levels' => ['high', 'moderate'],
            'missing_courses_only' => true,
            'grades' => [],              // Empty = all grades
        ],
        'default_course_type' => 'intervention',
        'default_duration_minutes' => 30,
        'require_moderation' => true,    // Auto-generated courses need approval
        'auto_enroll' => true,           // Auto-enroll target students
        'notify_on_generation' => true,  // Notify moderators
    ];

    /**
     * Get auto-course generation settings (merged with defaults).
     */
    public function getAutoCourseSettings(): array
    {
        $settings = $this->getSetting('auto_course_generation', []);

        return array_replace_recursive(self::DEFAULT_AUTO_COURSE_SETTINGS, $settings);
    }

    /**
     * Set auto-course generation settings.
     */
    public function setAutoCourseSettings(array $settings): void
    {
        $this->setSetting('auto_course_generation', $settings);
    }

    /**
     * Check if auto-course generation is enabled.
     */
    public function isAutoCourseGenerationEnabled(): bool
    {
        $settings = $this->getAutoCourseSettings();

        return $settings['enabled'] && $settings['schedule'] !== 'disabled';
    }

    /**
     * Get the schedule configuration for auto-course generation.
     */
    public function getAutoCourseSchedule(): array
    {
        $settings = $this->getAutoCourseSettings();

        return [
            'schedule' => $settings['schedule'],
            'time' => $settings['schedule_time'],
            'day' => $settings['schedule_day'],
            'date' => $settings['schedule_date'],
        ];
    }

    /**
     * Check if the current time matches the scheduled run time.
     */
    public function shouldRunAutoCourseGeneration(): bool
    {
        if (! $this->isAutoCourseGenerationEnabled()) {
            return false;
        }

        $settings = $this->getAutoCourseSettings();
        $now = now()->setTimezone($this->organization?->timezone ?? config('app.timezone'));

        // Check time (within 5 minute window)
        $scheduledTime = \Carbon\Carbon::parse($settings['schedule_time'], $now->timezone);
        $minutesDiff = abs($now->diffInMinutes($scheduledTime->setDate($now->year, $now->month, $now->day)));

        if ($minutesDiff > 5) {
            return false;
        }

        // Check day/date based on schedule type
        return match ($settings['schedule']) {
            'daily' => true,
            'weekly' => strtolower($now->format('l')) === strtolower($settings['schedule_day']),
            'monthly' => $now->day === (int) $settings['schedule_date'],
            default => false,
        };
    }
}
