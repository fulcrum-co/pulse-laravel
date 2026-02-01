<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'source_survey_id',
        'source_org_id',
        'title',
        'description',
        'survey_type',
        'creation_mode',
        'template_id',
        'creation_session_id',
        'questions',
        'interpretation_config',
        'status',
        'is_anonymous',
        'estimated_duration_minutes',
        'start_date',
        'end_date',
        'target_grades',
        'target_classrooms',
        'delivery_channels',
        'voice_config',
        'allow_voice_responses',
        'ai_follow_up_enabled',
        'llm_system_prompt',
        'scoring_config',
        'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'interpretation_config' => 'array',
        'target_grades' => 'array',
        'target_classrooms' => 'array',
        'delivery_channels' => 'array',
        'voice_config' => 'array',
        'scoring_config' => 'array',
        'is_anonymous' => 'boolean',
        'allow_voice_responses' => 'boolean',
        'ai_follow_up_enabled' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Creation mode constants
     */
    public const MODE_STATIC = 'static';

    public const MODE_CHAT = 'chat';

    public const MODE_VOICE = 'voice';

    public const MODE_AI_ASSISTED = 'ai_assisted';

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Get the organization that owns this survey.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the source survey this was pushed from.
     */
    public function sourceSurvey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'source_survey_id');
    }

    /**
     * Get the source organization this was pushed from.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get all surveys pushed from this survey.
     */
    public function pushedSurveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'source_survey_id');
    }

    /**
     * Push this survey to another organization.
     * Creates a copy with draft status for the target org to review.
     */
    public function pushToOrganization(Organization $targetOrg, ?int $pushedBy = null): self
    {
        $newSurvey = $this->replicate([
            'org_id',
            'source_survey_id',
            'source_org_id',
            'created_by',
            'status',
            'start_date',
            'end_date',
        ]);

        $newSurvey->org_id = $targetOrg->id;
        $newSurvey->source_survey_id = $this->id;
        $newSurvey->source_org_id = $this->org_id;
        $newSurvey->created_by = $pushedBy;
        $newSurvey->status = self::STATUS_DRAFT;
        $newSurvey->title = $this->title.' (from '.$this->organization->org_name.')';
        $newSurvey->save();

        return $newSurvey;
    }

    /**
     * Check if this survey was pushed from another organization.
     */
    public function wasPushed(): bool
    {
        return $this->source_survey_id !== null;
    }

    /**
     * Get the user who created this survey.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the template this survey was created from.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'template_id');
    }

    /**
     * Get the creation session.
     */
    public function creationSession(): BelongsTo
    {
        return $this->belongsTo(SurveyCreationSession::class, 'creation_session_id');
    }

    /**
     * Get all attempts for this survey.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /**
     * Get completed attempts for this survey.
     */
    public function completedAttempts(): HasMany
    {
        return $this->attempts()->where('status', 'completed');
    }

    /**
     * Get all deliveries for this survey.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(SurveyDelivery::class);
    }

    /**
     * Get all strategy survey assignments for this survey.
     */
    public function strategySurveyAssignments(): HasMany
    {
        return $this->hasMany(StrategySurveyAssignment::class);
    }

    /**
     * Get all strategic items (focus areas, objectives, activities) this survey is assigned to.
     */
    public function getAssignedStrategyItems(): \Illuminate\Support\Collection
    {
        return $this->strategySurveyAssignments()->with('assignable')->get()->pluck('assignable');
    }

    /**
     * Get question count.
     */
    public function getQuestionCountAttribute(): int
    {
        return count($this->questions ?? []);
    }

    /**
     * Get available delivery channels.
     */
    public function getAvailableChannelsAttribute(): array
    {
        return $this->delivery_channels ?? ['web'];
    }

    /**
     * Check if survey supports a specific channel.
     */
    public function supportsChannel(string $channel): bool
    {
        return in_array($channel, $this->available_channels);
    }

    /**
     * Scope to filter active surveys.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter by survey type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('survey_type', $type);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter by creation mode.
     */
    public function scopeCreationMode(Builder $query, string $mode): Builder
    {
        return $query->where('creation_mode', $mode);
    }

    /**
     * Scope to filter surveys that support a specific channel.
     */
    public function scopeSupportsChannel(Builder $query, string $channel): Builder
    {
        return $query->whereJsonContains('delivery_channels', $channel);
    }

    /**
     * Get a specific question by ID.
     */
    public function getQuestion(string $questionId): ?array
    {
        foreach ($this->questions ?? [] as $question) {
            if (($question['id'] ?? null) === $questionId) {
                return $question;
            }
        }

        return null;
    }

    /**
     * Get question by index.
     */
    public function getQuestionByIndex(int $index): ?array
    {
        return $this->questions[$index] ?? null;
    }

    /**
     * Calculate score for responses based on interpretation config.
     */
    public function calculateScore(array $responses): ?float
    {
        $config = $this->interpretation_config;
        if (! $config) {
            return null;
        }

        $method = $config['scoring_method'] ?? 'average';
        $scores = [];

        foreach ($this->questions ?? [] as $question) {
            $questionId = $question['id'] ?? null;
            if (! $questionId || ! isset($responses[$questionId])) {
                continue;
            }

            $response = $responses[$questionId];
            if ($question['type'] === 'scale' && is_numeric($response)) {
                $weight = $config['weights'][$questionId] ?? 1;
                $scores[] = (float) $response * $weight;
            }
        }

        if (empty($scores)) {
            return null;
        }

        return match ($method) {
            'average' => array_sum($scores) / count($scores),
            'weighted' => array_sum($scores) / array_sum(array_map(fn ($q) => $config['weights'][$q['id'] ?? ''] ?? 1, $this->questions ?? [])),
            'sum' => array_sum($scores),
            default => array_sum($scores) / count($scores),
        };
    }

    /**
     * Determine risk level based on score and config.
     */
    public function determineRiskLevel(float $score): string
    {
        $thresholds = $this->interpretation_config['risk_thresholds'] ?? [];

        if (isset($thresholds['high']) && $score <= $thresholds['high']) {
            return 'high';
        }
        if (isset($thresholds['medium']) && $score <= $thresholds['medium']) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get all available creation modes.
     */
    public static function getCreationModes(): array
    {
        return [
            self::MODE_STATIC => 'Form Builder',
            self::MODE_CHAT => 'AI Chat Assistant',
            self::MODE_VOICE => 'Voice Recording',
            self::MODE_AI_ASSISTED => 'AI Assisted',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}
