<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CohortMember extends Model
{
    // Roles
    public const ROLE_STUDENT = 'participant';
    public const ROLE_MENTOR = 'mentor';
    public const ROLE_FACILITATOR = 'facilitator';
    public const ROLE_ADMIN = 'admin';

    // Statuses
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_PAUSED = 'paused';

    // Enrollment sources
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_SELF_ENROLLED = 'self_enrolled';
    public const SOURCE_BULK_IMPORT = 'bulk_import';
    public const SOURCE_API = 'api';

    // Lead sources
    public const LEAD_WIDGET = 'widget';
    public const LEAD_LANDING_PAGE = 'landing_page';
    public const LEAD_REFERRAL = 'referral';
    public const LEAD_ORGANIC = 'organic';

    protected $fillable = [
        'cohort_id',
        'user_id',
        'role',
        'status',
        'progress_percent',
        'current_step_id',
        'enrolled_at',
        'started_at',
        'completed_at',
        'enrollment_source',
        'notes',
        'feedback',
        'analytics_data',
        'lead_source',
        'lead_source_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'lead_score',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'feedback' => 'array',
        'analytics_data' => 'array',
        'lead_score' => 'integer',
    ];

    protected $attributes = [
        'role' => self::ROLE_STUDENT,
        'status' => self::STATUS_ENROLLED,
        'progress_percent' => 0,
        'enrollment_source' => self::SOURCE_MANUAL,
        'lead_score' => 0,
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(MiniCourseStep::class, 'current_step_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CohortProgress::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeLearners(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    public function scopeMentors(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_MENTOR);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ENROLLED, self::STATUS_ACTIVE]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WITHDRAWN);
    }

    public function scopeFromWidget(Builder $query): Builder
    {
        return $query->where('lead_source', self::LEAD_WIDGET);
    }

    public function scopeByLeadSource(Builder $query, string $source): Builder
    {
        return $query->where('lead_source', $source);
    }

    // Helper methods
    public static function getRoleOptions(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            self::ROLE_STUDENT => $terminology->get('learner_singular'),
            self::ROLE_MENTOR => $terminology->get('mentor_singular'),
            self::ROLE_FACILITATOR => $terminology->get('facilitator_singular'),
            self::ROLE_ADMIN => $terminology->get('admin_label'),
        ];
    }

    public static function getStatusOptions(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            self::STATUS_ENROLLED => $terminology->get('enrolled_label'),
            self::STATUS_ACTIVE => $terminology->get('active_label'),
            self::STATUS_COMPLETED => $terminology->get('completed_label'),
            self::STATUS_WITHDRAWN => $terminology->get('withdrawn_label'),
            self::STATUS_PAUSED => $terminology->get('paused_label'),
        ];
    }

    public static function getLeadSources(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            self::LEAD_WIDGET => $terminology->get('lead_source_widget_label'),
            self::LEAD_LANDING_PAGE => $terminology->get('lead_source_landing_page_label'),
            self::LEAD_REFERRAL => $terminology->get('lead_source_referral_label'),
            self::LEAD_ORGANIC => $terminology->get('lead_source_organic_label'),
        ];
    }

    public function isLearner(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isMentor(): bool
    {
        return $this->role === self::ROLE_MENTOR;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_ACTIVE]);
    }

    public function start(): void
    {
        if ($this->started_at === null) {
            $this->update([
                'status' => self::STATUS_ACTIVE,
                'started_at' => now(),
            ]);
        }
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);
    }

    public function withdraw(): void
    {
        $this->update(['status' => self::STATUS_WITHDRAWN]);
    }

    public function pause(): void
    {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    public function resume(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function updateProgress(): void
    {
        $course = $this->cohort->course;
        $totalSteps = $course->steps()->count();

        if ($totalSteps === 0) {
            return;
        }

        $completedSteps = $this->progress()
            ->where('status', CohortProgress::STATUS_COMPLETED)
            ->count();

        $this->update([
            'progress_percent' => round(($completedSteps / $totalSteps) * 100),
        ]);

        // Auto-complete if all steps done
        if ($completedSteps >= $totalSteps) {
            $this->complete();
        }
    }

    /**
     * Get UTM tracking data.
     */
    public function getUtmData(): array
    {
        return array_filter([
            'source' => $this->utm_source,
            'medium' => $this->utm_medium,
            'campaign' => $this->utm_campaign,
        ]);
    }

    /**
     * Calculate time spent in course.
     */
    public function getTotalTimeSpentAttribute(): int
    {
        return $this->progress()->sum('time_spent_seconds');
    }
}
