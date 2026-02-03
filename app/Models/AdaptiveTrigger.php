<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdaptiveTrigger extends Model
{
    use SoftDeletes;

    // Trigger types
    public const TYPE_COURSE_SUGGESTION = 'course_suggestion';

    public const TYPE_COURSE_EDIT = 'course_edit';

    public const TYPE_PROVIDER_RECOMMENDATION = 'provider_recommendation';

    public const TYPE_INTERVENTION_ALERT = 'intervention_alert';

    // Output actions
    public const ACTION_SUGGEST_FOR_REVIEW = 'suggest_for_review';

    public const ACTION_AUTO_CREATE = 'auto_create';

    public const ACTION_AUTO_ENROLL = 'auto_enroll';

    public const ACTION_NOTIFY = 'notify';

    // Input source types
    public const INPUT_QUANTITATIVE = 'quantitative';

    public const INPUT_QUALITATIVE = 'qualitative';

    public const INPUT_BEHAVIORAL = 'behavioral';

    public const INPUT_EXPLICIT = 'explicit';

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'trigger_type',
        'input_sources',
        'conditions',
        'ai_interpretation_enabled',
        'ai_prompt_context',
        'output_action',
        'output_config',
        'cooldown_hours',
        'active',
        'last_triggered_at',
        'triggered_count',
        'created_by',
    ];

    protected $casts = [
        'input_sources' => 'array',
        'conditions' => 'array',
        'output_config' => 'array',
        'ai_interpretation_enabled' => 'boolean',
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected $attributes = [
        'active' => true,
        'ai_interpretation_enabled' => false,
        'cooldown_hours' => 24,
        'triggered_count' => 0,
    ];

    /**
     * Get available trigger types.
     */
    public static function getTriggerTypes(): array
    {
        return [
            self::TYPE_COURSE_SUGGESTION => [
                'label' => 'Course Suggestion',
                'description' => 'Suggest a mini-course to a participant',
            ],
            self::TYPE_COURSE_EDIT => [
                'label' => 'Course Edit',
                'description' => 'Suggest edits to an existing course',
            ],
            self::TYPE_PROVIDER_RECOMMENDATION => [
                'label' => 'Provider Recommendation',
                'description' => 'Recommend a provider connection',
            ],
            self::TYPE_INTERVENTION_ALERT => [
                'label' => 'Intervention Alert',
                'description' => 'Send an intervention alert',
            ],
        ];
    }

    /**
     * Get available output actions.
     */
    public static function getOutputActions(): array
    {
        return [
            self::ACTION_SUGGEST_FOR_REVIEW => [
                'label' => 'Suggest for Review',
                'description' => 'Create suggestion for human review',
            ],
            self::ACTION_AUTO_CREATE => [
                'label' => 'Auto Create',
                'description' => 'Automatically create and suggest',
            ],
            self::ACTION_AUTO_ENROLL => [
                'label' => 'Auto Enroll',
                'description' => 'Automatically enroll participant',
            ],
            self::ACTION_NOTIFY => [
                'label' => 'Notify Only',
                'description' => 'Send notification without action',
            ],
        ];
    }

    /**
     * Get available input source types.
     */
    public static function getInputSourceTypes(): array
    {
        return [
            self::INPUT_QUANTITATIVE => [
                'label' => 'Quantitative Data',
                'description' => 'Metrics, levels, attendance, scores',
                'examples' => ['GPA', 'Attendance Rate', 'Survey Scores', 'Risk Level'],
            ],
            self::INPUT_QUALITATIVE => [
                'label' => 'Qualitative Data',
                'description' => 'Notes, survey responses, narratives',
                'examples' => ['Instructor Notes', 'Survey Text Responses', 'Support Person Observations'],
            ],
            self::INPUT_BEHAVIORAL => [
                'label' => 'Behavioral Data',
                'description' => 'Usage patterns, engagement metrics',
                'examples' => ['Login Frequency', 'Course Completion', 'Resource Access'],
            ],
            self::INPUT_EXPLICIT => [
                'label' => 'Explicit Flags',
                'description' => 'Explicit indicators and statuses',
                'examples' => ['IEP Status', 'ELL Status', 'Manual Flags', 'Special Programs'],
            ],
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
     * Scope to active triggers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope by trigger type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('trigger_type', $type);
    }

    /**
     * Scope by output action.
     */
    public function scopeWithAction(Builder $query, string $action): Builder
    {
        return $query->where('output_action', $action);
    }

    /**
     * Scope to triggers with AI interpretation.
     */
    public function scopeWithAi(Builder $query): Builder
    {
        return $query->where('ai_interpretation_enabled', true);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Check if trigger is on cooldown.
     */
    public function isOnCooldown(): bool
    {
        if (! $this->last_triggered_at || ! $this->cooldown_hours) {
            return false;
        }

        return $this->last_triggered_at->addHours($this->cooldown_hours)->isFuture();
    }

    /**
     * Check if trigger uses AI interpretation.
     */
    public function usesAi(): bool
    {
        return $this->ai_interpretation_enabled === true;
    }

    /**
     * Mark trigger as fired.
     */
    public function markTriggered(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'triggered_count' => $this->triggered_count + 1,
        ]);
    }

    /**
     * Get condition by key.
     */
    public function getCondition(string $key, $default = null)
    {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * Set condition value.
     */
    public function setCondition(string $key, $value): void
    {
        $conditions = $this->conditions ?? [];
        $conditions[$key] = $value;
        $this->update(['conditions' => $conditions]);
    }

    /**
     * Get output config value.
     */
    public function getOutputConfig(string $key, $default = null)
    {
        return $this->output_config[$key] ?? $default;
    }

    /**
     * Check if trigger uses input source type.
     */
    public function usesInputSource(string $sourceType): bool
    {
        return in_array($sourceType, $this->input_sources ?? []);
    }

    /**
     * Add input source type.
     */
    public function addInputSource(string $sourceType): void
    {
        $sources = $this->input_sources ?? [];
        if (! in_array($sourceType, $sources)) {
            $sources[] = $sourceType;
            $this->update(['input_sources' => $sources]);
        }
    }

    /**
     * Remove input source type.
     */
    public function removeInputSource(string $sourceType): void
    {
        $sources = $this->input_sources ?? [];
        $sources = array_filter($sources, fn ($s) => $s !== $sourceType);
        $this->update(['input_sources' => array_values($sources)]);
    }

    /**
     * Activate trigger.
     */
    public function activate(): void
    {
        $this->update(['active' => true]);
    }

    /**
     * Deactivate trigger.
     */
    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    /**
     * Duplicate trigger.
     */
    public function duplicate(): self
    {
        $newTrigger = $this->replicate(['id', 'created_at', 'updated_at', 'last_triggered_at', 'triggered_count']);
        $newTrigger->name = $this->name.' (Copy)';
        $newTrigger->triggered_count = 0;
        $newTrigger->last_triggered_at = null;
        $newTrigger->save();

        return $newTrigger;
    }
}
