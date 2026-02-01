<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class MiniCourse extends Model
{
    use SoftDeletes, Searchable;

    // Course types
    public const TYPE_INTERVENTION = 'intervention';
    public const TYPE_ENRICHMENT = 'enrichment';
    public const TYPE_SKILL_BUILDING = 'skill_building';
    public const TYPE_WELLNESS = 'wellness';
    public const TYPE_ACADEMIC = 'academic';
    public const TYPE_BEHAVIORAL = 'behavioral';

    // Creation sources
    public const SOURCE_AI_GENERATED = 'ai_generated';
    public const SOURCE_HUMAN_CREATED = 'human_created';
    public const SOURCE_HYBRID = 'hybrid';
    public const SOURCE_TEMPLATE = 'template';

    // Statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    // Generation triggers
    public const TRIGGER_MANUAL = 'manual';
    public const TRIGGER_SCHEDULED = 'scheduled';
    public const TRIGGER_SIGNAL = 'signal';

    // Approval statuses
    public const APPROVAL_PENDING = 'pending_review';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';
    public const APPROVAL_REVISION = 'revision_requested';

    // Target entity types
    public const TARGET_STUDENT = 'student';
    public const TARGET_TEACHER = 'teacher';
    public const TARGET_DEPARTMENT = 'department';
    public const TARGET_CONTACT_LIST = 'contact_list';

    protected $fillable = [
        'org_id',
        'source_course_id',
        'source_org_id',
        'current_version_id',
        'title',
        'description',
        'objectives',
        'rationale',
        'expected_experience',
        'course_type',
        'creation_source',
        'ai_generation_context',
        'target_grades',
        'target_risk_levels',
        'target_needs',
        'estimated_duration_minutes',
        'difficulty_level',
        'status',
        'is_public',
        'is_template',
        'analytics_config',
        'created_by',
        'published_at',
        // Auto-generation fields
        'generation_trigger',
        'target_entity_type',
        'target_entity_id',
        'generation_signals',
        'auto_generated_at',
        // Approval fields
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        // Generation tracking
        'generation_request_id',
        'template_id',
        'assigned_student_ids',
        'assigned_group_id',
    ];

    protected $casts = [
        'objectives' => 'array',
        'ai_generation_context' => 'array',
        'target_grades' => 'array',
        'target_risk_levels' => 'array',
        'target_needs' => 'array',
        'analytics_config' => 'array',
        'generation_signals' => 'array',
        'is_public' => 'boolean',
        'is_template' => 'boolean',
        'published_at' => 'datetime',
        'auto_generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'assigned_student_ids' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'creation_source' => self::SOURCE_HUMAN_CREATED,
        'is_public' => false,
        'is_template' => false,
    ];

    /**
     * Get available course types.
     */
    public static function getCourseTypes(): array
    {
        return [
            self::TYPE_INTERVENTION => 'Intervention',
            self::TYPE_ENRICHMENT => 'Enrichment',
            self::TYPE_SKILL_BUILDING => 'Skill Building',
            self::TYPE_WELLNESS => 'Wellness',
            self::TYPE_ACADEMIC => 'Academic',
            self::TYPE_BEHAVIORAL => 'Behavioral',
        ];
    }

    /**
     * Get available creation sources.
     */
    public static function getCreationSources(): array
    {
        return [
            self::SOURCE_AI_GENERATED => 'AI Generated',
            self::SOURCE_HUMAN_CREATED => 'Human Created',
            self::SOURCE_HYBRID => 'Hybrid (AI + Human)',
            self::SOURCE_TEMPLATE => 'From Template',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Source course relationship (for forks).
     */
    public function sourceCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class, 'source_course_id');
    }

    /**
     * Source organization relationship.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Current version relationship.
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(MiniCourseVersion::class, 'current_version_id');
    }

    /**
     * All versions of this course.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MiniCourseVersion::class)->orderByDesc('version_number');
    }

    /**
     * Steps in this course.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(MiniCourseStep::class)->orderBy('sort_order');
    }

    /**
     * Enrollments in this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(MiniCourseEnrollment::class);
    }

    /**
     * Suggestions for this course.
     */
    public function suggestions(): HasMany
    {
        return $this->hasMany(MiniCourseSuggestion::class);
    }

    /**
     * Forked courses from this one.
     */
    public function forks(): HasMany
    {
        return $this->hasMany(MiniCourse::class, 'source_course_id');
    }

    /**
     * Approval workflow relationship.
     */
    public function approvalWorkflow(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CourseApprovalWorkflow::class);
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Target entity (polymorphic - student, user/teacher, department).
     */
    public function targetEntity(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('target_entity', 'target_entity_type', 'target_entity_id');
    }

    /**
     * Generation request that created this course.
     */
    public function generationRequest(): BelongsTo
    {
        return $this->belongsTo(CourseGenerationRequest::class, 'generation_request_id');
    }

    /**
     * Template used to generate this course.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CourseTemplate::class, 'template_id');
    }

    /**
     * Scope to active courses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to draft courses.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to templates.
     */
    public function scopeTemplates(Builder $query): Builder
    {
        return $query->where('is_template', true);
    }

    /**
     * Scope to public courses.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope by course type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('course_type', $type);
    }

    /**
     * Scope by creation source.
     */
    public function scopeCreatedBy(Builder $query, string $source): Builder
    {
        return $query->where('creation_source', $source);
    }

    /**
     * Scope for target grade.
     */
    public function scopeForGrade(Builder $query, int|string $grade): Builder
    {
        return $query->whereJsonContains('target_grades', $grade);
    }

    /**
     * Scope for target risk level.
     */
    public function scopeForRiskLevel(Builder $query, string $riskLevel): Builder
    {
        return $query->whereJsonContains('target_risk_levels', $riskLevel);
    }

    /**
     * Scope for target need.
     */
    public function scopeForNeed(Builder $query, string $need): Builder
    {
        return $query->whereJsonContains('target_needs', $need);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to auto-generated courses.
     */
    public function scopeAutoGenerated(Builder $query): Builder
    {
        return $query->whereNotNull('auto_generated_at');
    }

    /**
     * Scope by generation trigger.
     */
    public function scopeByTrigger(Builder $query, string $trigger): Builder
    {
        return $query->where('generation_trigger', $trigger);
    }

    /**
     * Scope to courses pending approval.
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    /**
     * Scope by approval status.
     */
    public function scopeByApprovalStatus(Builder $query, string $status): Builder
    {
        return $query->where('approval_status', $status);
    }

    /**
     * Scope for target entity.
     */
    public function scopeForTarget(Builder $query, string $type, int $id): Builder
    {
        return $query->where('target_entity_type', $type)->where('target_entity_id', $id);
    }

    /**
     * Check if course is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->published_at !== null;
    }

    /**
     * Check if course is AI-generated.
     */
    public function isAiGenerated(): bool
    {
        return in_array($this->creation_source, [self::SOURCE_AI_GENERATED, self::SOURCE_HYBRID]);
    }

    /**
     * Publish the course.
     */
    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'published_at' => now(),
        ]);
    }

    /**
     * Archive the course.
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Create a new version snapshot.
     */
    public function createVersion(string $changeSummary = null, int $userId = null): MiniCourseVersion
    {
        $latestVersion = $this->versions()->max('version_number') ?? 0;

        $version = $this->versions()->create([
            'version_number' => $latestVersion + 1,
            'title' => $this->title,
            'description' => $this->description,
            'objectives' => $this->objectives,
            'rationale' => $this->rationale,
            'expected_experience' => $this->expected_experience,
            'steps_snapshot' => $this->steps()->with(['resource', 'provider', 'program'])->get()->toArray(),
            'change_summary' => $changeSummary,
            'created_by' => $userId ?? auth()->id() ?? $this->created_by,
        ]);

        $this->update(['current_version_id' => $version->id]);

        return $version;
    }

    /**
     * Duplicate the course.
     */
    public function duplicate(int $orgId = null, int $userId = null): self
    {
        $newCourse = $this->replicate(['id', 'created_at', 'updated_at', 'published_at', 'current_version_id']);
        $newCourse->org_id = $orgId ?? $this->org_id;
        $newCourse->source_course_id = $this->id;
        $newCourse->source_org_id = $this->org_id;
        $newCourse->created_by = $userId ?? auth()->id();
        $newCourse->status = self::STATUS_DRAFT;
        $newCourse->title = $this->title . ' (Copy)';
        $newCourse->is_template = false;
        $newCourse->save();

        // Duplicate steps
        foreach ($this->steps as $step) {
            $newStep = $step->replicate(['id', 'created_at', 'updated_at', 'mini_course_id']);
            $newStep->mini_course_id = $newCourse->id;
            $newStep->save();
        }

        return $newCourse;
    }

    /**
     * Get total estimated duration from steps.
     */
    public function getCalculatedDurationAttribute(): int
    {
        return $this->steps()->sum('estimated_duration_minutes') ?? 0;
    }

    /**
     * Get step count.
     */
    public function getStepCountAttribute(): int
    {
        return $this->steps()->count();
    }

    /**
     * Get enrollment count.
     */
    public function getEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    /**
     * Get completion count.
     */
    public function getCompletionCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }

    /**
     * Get completion rate.
     */
    public function getCompletionRateAttribute(): ?float
    {
        $total = $this->enrollment_count;
        if ($total === 0) {
            return null;
        }
        return round(($this->completion_count / $total) * 100, 1);
    }

    /**
     * Check if course is auto-generated.
     */
    public function isAutoGenerated(): bool
    {
        return $this->auto_generated_at !== null;
    }

    /**
     * Check if course is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    /**
     * Check if course is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    /**
     * Check if course needs revision.
     */
    public function needsRevision(): bool
    {
        return $this->approval_status === self::APPROVAL_REVISION;
    }

    /**
     * Get available generation triggers.
     */
    public static function getGenerationTriggers(): array
    {
        return [
            self::TRIGGER_MANUAL => 'Manual Request',
            self::TRIGGER_SCHEDULED => 'Scheduled Batch',
            self::TRIGGER_SIGNAL => 'Real-time Signal',
        ];
    }

    /**
     * Get available approval statuses.
     */
    public static function getApprovalStatuses(): array
    {
        return [
            self::APPROVAL_PENDING => 'Pending Review',
            self::APPROVAL_APPROVED => 'Approved',
            self::APPROVAL_REJECTED => 'Rejected',
            self::APPROVAL_REVISION => 'Revision Requested',
        ];
    }

    /**
     * Get available target entity types.
     */
    public static function getTargetEntityTypes(): array
    {
        return [
            self::TARGET_STUDENT => 'Student',
            self::TARGET_TEACHER => 'Teacher',
            self::TARGET_DEPARTMENT => 'Department',
            self::TARGET_CONTACT_LIST => 'Contact List',
        ];
    }

    /**
     * Get the indexable data array for the model (Meilisearch).
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'org_id' => $this->org_id,
            'title' => $this->title,
            'description' => $this->description,
            'objectives' => is_array($this->objectives) ? implode(' ', $this->objectives) : $this->objectives,
            'rationale' => $this->rationale,
            'course_type' => $this->course_type,
            'creation_source' => $this->creation_source,
            'status' => $this->status,
            'target_grades' => $this->target_grades ?? [],
            'target_risk_levels' => $this->target_risk_levels ?? [],
            'target_needs' => $this->target_needs ?? [],
            'is_template' => (bool) $this->is_template,
            'is_public' => (bool) $this->is_public,
            'approval_status' => $this->approval_status,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'calculated_duration_minutes' => $this->getCalculatedDuration(),
            'created_at' => $this->created_at?->getTimestamp(),
            'updated_at' => $this->updated_at?->getTimestamp(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        // Don't index deleted or draft courses (unless they're templates)
        if ($this->trashed()) {
            return false;
        }

        // Templates should be searchable even if draft
        if ($this->is_template) {
            return true;
        }

        // Only index active courses
        return $this->status === self::STATUS_ACTIVE;
    }
}
