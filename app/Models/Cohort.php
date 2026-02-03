<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Cohort extends Model
{
    use SoftDeletes;

    // Visibility statuses (for lead generation)
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_GATED = 'gated';
    public const VISIBILITY_PRIVATE = 'private';

    // Cohort statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ENROLLMENT_OPEN = 'enrollment_open';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    // Community types
    public const COMMUNITY_DISCUSSION_BOARD = 'discussion_board';
    public const COMMUNITY_SLACK = 'slack';
    public const COMMUNITY_DISCORD = 'discord';
    public const COMMUNITY_NONE = 'none';

    protected $fillable = [
        'org_id',
        'mini_course_id',
        'semester_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'visibility_status',
        'status',
        'max_capacity',
        'allow_self_enrollment',
        'drip_content',
        'drip_schedule',
        'live_sessions',
        'community_type',
        'community_url',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_capacity' => 'integer',
        'allow_self_enrollment' => 'boolean',
        'drip_content' => 'boolean',
        'drip_schedule' => 'array',
        'live_sessions' => 'array',
    ];

    protected $attributes = [
        'visibility_status' => self::VISIBILITY_PRIVATE,
        'status' => self::STATUS_DRAFT,
        'allow_self_enrollment' => false,
        'drip_content' => false,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class, 'mini_course_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CohortMember::class);
    }

    public function students(): HasMany
    {
        return $this->members()->where('role', CohortMember::ROLE_STUDENT);
    }

    public function mentors(): HasMany
    {
        return $this->members()->where('role', CohortMember::ROLE_MENTOR);
    }

    public function facilitators(): HasMany
    {
        return $this->members()->where('role', CohortMember::ROLE_FACILITATOR);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, CohortMember::class, 'cohort_id', 'id', 'id', 'user_id');
    }

    // Scopes
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility_status', self::VISIBILITY_PUBLIC);
    }

    public function scopeGated(Builder $query): Builder
    {
        return $query->where('visibility_status', self::VISIBILITY_GATED);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('visibility_status', [self::VISIBILITY_PUBLIC, self::VISIBILITY_GATED]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeEnrollmentOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENROLLMENT_OPEN);
    }

    public function scopeAcceptingEnrollments(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ENROLLMENT_OPEN, self::STATUS_ACTIVE])
                     ->where('allow_self_enrollment', true);
    }

    public function scopeForSemester(Builder $query, int $semesterId): Builder
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeForCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('mini_course_id', $courseId);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now()->toDateString());
    }

    // Helper methods
    public static function getVisibilityOptions(): array
    {
        return [
            self::VISIBILITY_PUBLIC => 'Public (SEO indexed, no sign-up required)',
            self::VISIBILITY_GATED => 'Gated (Email required for access)',
            self::VISIBILITY_PRIVATE => 'Private (Enrolled members only)',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ENROLLMENT_OPEN => 'Enrollment Open',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getCommunityTypes(): array
    {
        return [
            self::COMMUNITY_DISCUSSION_BOARD => 'Built-in Discussion Board',
            self::COMMUNITY_SLACK => 'Slack Channel',
            self::COMMUNITY_DISCORD => 'Discord Server',
            self::COMMUNITY_NONE => 'No Community',
        ];
    }

    public function isPublic(): bool
    {
        return $this->visibility_status === self::VISIBILITY_PUBLIC;
    }

    public function isGated(): bool
    {
        return $this->visibility_status === self::VISIBILITY_GATED;
    }

    public function isPrivate(): bool
    {
        return $this->visibility_status === self::VISIBILITY_PRIVATE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isEnrollmentOpen(): bool
    {
        return in_array($this->status, [self::STATUS_ENROLLMENT_OPEN, self::STATUS_ACTIVE]);
    }

    public function hasCapacity(): bool
    {
        if ($this->max_capacity === null) {
            return true;
        }
        return $this->members()->count() < $this->max_capacity;
    }

    public function canEnroll(): bool
    {
        return $this->isEnrollmentOpen() && $this->hasCapacity() && $this->allow_self_enrollment;
    }

    public function getEnrollmentCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function getCompletionRateAttribute(): ?float
    {
        $total = $this->members()->count();
        if ($total === 0) {
            return null;
        }
        $completed = $this->members()->where('status', CohortMember::STATUS_COMPLETED)->count();
        return round(($completed / $total) * 100, 1);
    }

    public function getAverageProgressAttribute(): float
    {
        return $this->members()->avg('progress_percent') ?? 0;
    }

    /**
     * Get steps that are currently available based on drip schedule.
     */
    public function getAvailableSteps(): \Illuminate\Support\Collection
    {
        if (!$this->drip_content || empty($this->drip_schedule)) {
            return $this->course->steps;
        }

        $now = now();
        $startDate = $this->start_date;
        $availableStepIds = [];

        foreach ($this->drip_schedule as $schedule) {
            $releaseDate = $startDate->copy()->addDays($schedule['days_after_start'] ?? 0);
            if ($releaseDate <= $now) {
                $availableStepIds[] = $schedule['step_id'];
            }
        }

        return $this->course->steps()->whereIn('id', $availableStepIds)->get();
    }

    /**
     * Get upcoming live sessions.
     */
    public function getUpcomingLiveSessions(): array
    {
        if (empty($this->live_sessions)) {
            return [];
        }

        $now = now();
        return collect($this->live_sessions)
            ->filter(fn($session) => isset($session['datetime']) && $session['datetime'] > $now->toDateTimeString())
            ->sortBy('datetime')
            ->values()
            ->toArray();
    }
}
