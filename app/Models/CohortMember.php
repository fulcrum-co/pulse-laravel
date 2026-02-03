<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CohortMember extends Model
{
    // Roles
    public const ROLE_STUDENT = 'student';
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
    public function scopeStudents(Builder $query): Builder
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
        return [
            self::ROLE_STUDENT => 'Student',
            self::ROLE_MENTOR => 'Mentor',
            self::ROLE_FACILITATOR => 'Facilitator',
            self::ROLE_ADMIN => 'Admin',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ENROLLED => 'Enrolled',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_WITHDRAWN => 'Withdrawn',
            self::STATUS_PAUSED => 'Paused',
        ];
    }

    public static function getLeadSources(): array
    {
        return [
            self::LEAD_WIDGET => 'Embedded Widget',
            self::LEAD_LANDING_PAGE => 'Landing Page',
            self::LEAD_REFERRAL => 'Referral',
            self::LEAD_ORGANIC => 'Organic',
        ];
    }

    public function isStudent(): bool
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
