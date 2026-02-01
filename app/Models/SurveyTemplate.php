<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'template_type',
        'questions',
        'interpretation_config',
        'delivery_defaults',
        'tags',
        'is_public',
        'is_featured',
        'usage_count',
        'estimated_duration_minutes',
        'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'interpretation_config' => 'array',
        'delivery_defaults' => 'array',
        'tags' => 'array',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Template type constants
     */
    public const TYPE_WELLNESS_CHECK = 'wellness_check';

    public const TYPE_ACADEMIC_STRESS = 'academic_stress';

    public const TYPE_SEL_SCREENER = 'sel_screener';

    public const TYPE_BEHAVIORAL = 'behavioral';

    public const TYPE_CUSTOM = 'custom';

    /**
     * Get the organization that owns this template.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get surveys created from this template.
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'template_id');
    }

    /**
     * Scope to get templates available to an organization.
     */
    public function scopeAvailableTo(Builder $query, int $orgId): Builder
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('is_public', true)
                ->orWhere('org_id', $orgId);
        });
    }

    /**
     * Scope to filter by template type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('template_type', $type);
    }

    /**
     * Scope to get featured templates.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get public templates.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Increment usage count when template is used.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Create a survey from this template.
     */
    public function createSurvey(int $orgId, int $createdBy, array $overrides = []): Survey
    {
        $this->incrementUsage();

        $surveyData = array_merge([
            'org_id' => $orgId,
            'title' => $this->name,
            'description' => $this->description,
            'survey_type' => $this->getDefaultSurveyType(),
            'questions' => $this->questions,
            'interpretation_config' => $this->interpretation_config,
            'delivery_channels' => $this->delivery_defaults['channels'] ?? ['web'],
            'template_id' => $this->id,
            'creation_mode' => 'static',
            'status' => 'draft',
            'created_by' => $createdBy,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
        ], $overrides);

        return Survey::create($surveyData);
    }

    /**
     * Get the default survey type based on template type.
     */
    protected function getDefaultSurveyType(): string
    {
        return match ($this->template_type) {
            self::TYPE_WELLNESS_CHECK => 'wellness',
            self::TYPE_ACADEMIC_STRESS => 'academic',
            self::TYPE_SEL_SCREENER => 'wellness',
            self::TYPE_BEHAVIORAL => 'behavioral',
            default => 'custom',
        };
    }

    /**
     * Get question count.
     */
    public function getQuestionCountAttribute(): int
    {
        return count($this->questions ?? []);
    }

    /**
     * Get all available template types.
     */
    public static function getTemplateTypes(): array
    {
        return [
            self::TYPE_WELLNESS_CHECK => 'Wellness Check-In',
            self::TYPE_ACADEMIC_STRESS => 'Academic Stress Assessment',
            self::TYPE_SEL_SCREENER => 'Social-Emotional Screener',
            self::TYPE_BEHAVIORAL => 'Behavioral Assessment',
            self::TYPE_CUSTOM => 'Custom Template',
        ];
    }
}
