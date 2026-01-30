<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'title',
        'description',
        'collection_type',
        'data_source',
        'survey_id',
        'inline_questions',
        'format_mode',
        'status',
        'settings',
        'contact_scope',
        'reminder_config',
        'created_by',
        'archived_at',
    ];

    protected $casts = [
        'inline_questions' => 'array',
        'settings' => 'array',
        'contact_scope' => 'array',
        'reminder_config' => 'array',
        'archived_at' => 'datetime',
    ];

    /**
     * Collection type constants
     */
    public const TYPE_RECURRING = 'recurring';
    public const TYPE_ONE_TIME = 'one_time';
    public const TYPE_EVENT_TRIGGERED = 'event_triggered';

    /**
     * Data source constants
     */
    public const SOURCE_SURVEY = 'survey';
    public const SOURCE_INLINE = 'inline';
    public const SOURCE_HYBRID = 'hybrid';

    /**
     * Format mode constants
     */
    public const FORMAT_CONVERSATIONAL = 'conversational';
    public const FORMAT_FORM = 'form';
    public const FORMAT_GRID = 'grid';

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Get the organization that owns this collection.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this collection.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the linked survey (if any).
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get all schedules for this collection.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CollectionSchedule::class);
    }

    /**
     * Get all sessions for this collection.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(CollectionSession::class);
    }

    /**
     * Get all entries for this collection.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class);
    }

    /**
     * Get all reminders for this collection.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(CollectionReminder::class);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter active collections.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get all questions (from survey, inline, or merged).
     */
    public function getQuestions(): array
    {
        if ($this->data_source === self::SOURCE_SURVEY && $this->survey) {
            return $this->survey->questions ?? [];
        }

        if ($this->data_source === self::SOURCE_INLINE) {
            return $this->inline_questions ?? [];
        }

        if ($this->data_source === self::SOURCE_HYBRID && $this->survey) {
            $surveyQuestions = $this->survey->questions ?? [];
            $inlineQuestions = $this->inline_questions ?? [];
            return array_merge($surveyQuestions, $inlineQuestions);
        }

        return $this->inline_questions ?? [];
    }

    /**
     * Get the active schedule for this collection.
     */
    public function getActiveSchedule(): ?CollectionSchedule
    {
        return $this->schedules()->where('is_active', true)->first();
    }

    /**
     * Check if collection is recurring.
     */
    public function isRecurring(): bool
    {
        return $this->collection_type === self::TYPE_RECURRING;
    }

    /**
     * Check if collection uses a survey.
     */
    public function usesSurvey(): bool
    {
        return in_array($this->data_source, [self::SOURCE_SURVEY, self::SOURCE_HYBRID]);
    }

    /**
     * Get statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Get collection types for dropdown.
     */
    public static function getCollectionTypes(): array
    {
        return [
            self::TYPE_RECURRING => 'Recurring',
            self::TYPE_ONE_TIME => 'One Time',
            self::TYPE_EVENT_TRIGGERED => 'Event Triggered',
        ];
    }

    /**
     * Get data sources for dropdown.
     */
    public static function getDataSources(): array
    {
        return [
            self::SOURCE_INLINE => 'Custom Questions',
            self::SOURCE_SURVEY => 'Use Existing Survey',
            self::SOURCE_HYBRID => 'Survey + Additional Questions',
        ];
    }

    /**
     * Get format modes for dropdown.
     */
    public static function getFormatModes(): array
    {
        return [
            self::FORMAT_FORM => 'Form',
            self::FORMAT_CONVERSATIONAL => 'Conversational',
            self::FORMAT_GRID => 'Bulk Grid',
        ];
    }
}
