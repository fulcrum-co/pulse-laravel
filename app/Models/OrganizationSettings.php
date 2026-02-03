<?php

namespace App\Models;

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
        'terminology',
    ];

    protected $casts = [
        'status_labels' => 'array',
        'risk_labels' => 'array',
        'settings' => 'array',
        'terminology' => 'array',
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
     * Default terminology labels (industry-agnostic).
     */
    public const DEFAULT_TERMINOLOGY = [
        // Time periods
        'period_singular' => 'Semester',
        'period_plural' => 'Semesters',
        // Learning units
        'course_singular' => 'Course',
        'course_plural' => 'Courses',
        'module_singular' => 'Module',
        'module_plural' => 'Modules',
        'step_singular' => 'Step',
        'step_plural' => 'Steps',
        'lesson_singular' => 'Lesson',
        'lesson_plural' => 'Lessons',
        // People
        'learner_singular' => 'Learner',
        'learner_plural' => 'Learners',
        'instructor_singular' => 'Instructor',
        'instructor_plural' => 'Instructors',
        'mentor_singular' => 'Mentor',
        'mentor_plural' => 'Mentors',
        'facilitator_singular' => 'Facilitator',
        'facilitator_plural' => 'Facilitators',
        // Groups
        'cohort_singular' => 'Cohort',
        'cohort_plural' => 'Cohorts',
        'organization_singular' => 'Organization',
        'organization_plural' => 'Organizations',
        // Credentials
        'certificate_singular' => 'Certificate',
        'certificate_plural' => 'Certificates',
        'badge_singular' => 'Badge',
        'badge_plural' => 'Badges',
        // Actions
        'enroll_action' => 'Enroll',
        'complete_action' => 'Complete',
        'progress_label' => 'Progress',
    ];

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
     * Get a terminology label (custom or default).
     */
    public function getTerm(string $key): string
    {
        $terminology = $this->terminology ?? [];

        return $terminology[$key] ?? self::DEFAULT_TERMINOLOGY[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get all terminology labels (merged with defaults).
     */
    public function getAllTerminology(): array
    {
        return array_merge(self::DEFAULT_TERMINOLOGY, $this->terminology ?? []);
    }

    /**
     * Set a terminology label.
     */
    public function setTerm(string $key, string $value): void
    {
        $terminology = $this->terminology ?? [];
        $terminology[$key] = $value;
        $this->terminology = $terminology;
        $this->save();
    }

    /**
     * Set multiple terminology labels at once.
     */
    public function setTerminology(array $labels): void
    {
        $terminology = $this->terminology ?? [];
        $this->terminology = array_merge($terminology, $labels);
        $this->save();
    }

    /**
     * Reset terminology to defaults.
     */
    public function resetTerminology(): void
    {
        $this->terminology = [];
        $this->save();
    }

    /**
     * Get terminology categories for admin UI.
     */
    public static function getTerminologyCategories(): array
    {
        return [
            'Time Periods' => ['period_singular', 'period_plural'],
            'Learning Units' => ['course_singular', 'course_plural', 'module_singular', 'module_plural', 'step_singular', 'step_plural', 'lesson_singular', 'lesson_plural'],
            'People' => ['learner_singular', 'learner_plural', 'instructor_singular', 'instructor_plural', 'mentor_singular', 'mentor_plural', 'facilitator_singular', 'facilitator_plural'],
            'Groups' => ['cohort_singular', 'cohort_plural', 'organization_singular', 'organization_plural'],
            'Credentials' => ['certificate_singular', 'certificate_plural', 'badge_singular', 'badge_plural'],
            'Actions & Labels' => ['enroll_action', 'complete_action', 'progress_label'],
        ];
    }
}
