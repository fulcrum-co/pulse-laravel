<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use SoftDeletes;

    protected $table = 'question_banks';

    protected $fillable = [
        'org_id',
        'category',
        'subcategory',
        'question_text',
        'question_type',
        'options',
        'interpretation_rules',
        'scoring_weights',
        'audio_file_path',
        'audio_disk',
        'is_public',
        'is_validated',
        'tags',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'options' => 'array',
        'interpretation_rules' => 'array',
        'scoring_weights' => 'array',
        'tags' => 'array',
        'is_public' => 'boolean',
        'is_validated' => 'boolean',
    ];

    /**
     * Question type constants
     */
    public const TYPE_SCALE = 'scale';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_TEXT = 'text';

    public const TYPE_VOICE = 'voice';

    public const TYPE_MATRIX = 'matrix';

    /**
     * Category constants
     */
    public const CATEGORY_WELLNESS = 'wellness';

    public const CATEGORY_ACADEMIC = 'academic';

    public const CATEGORY_BEHAVIORAL = 'behavioral';

    public const CATEGORY_SEL = 'sel';

    public const CATEGORY_CUSTOM = 'custom';

    /**
     * Get the organization that owns this question.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this question.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get questions available to an organization.
     * Includes public questions and org-specific questions.
     */
    public function scopeAvailableTo(Builder $query, int $orgId): Builder
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('is_public', true)
                ->orWhere('org_id', $orgId);
        });
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by question type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    /**
     * Scope to get validated questions only.
     */
    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('is_validated', true);
    }

    /**
     * Scope to search by tags.
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope to search by text.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('question_text', 'like', "%{$search}%");
    }

    /**
     * Increment usage count when question is used in a survey.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Convert to survey question format.
     */
    public function toSurveyQuestion(?string $id = null): array
    {
        $question = [
            'id' => $id ?? 'q_'.$this->id,
            'type' => $this->question_type,
            'question' => $this->question_text,
            'bank_question_id' => $this->id,
        ];

        // Add type-specific fields
        if ($this->options) {
            if ($this->question_type === self::TYPE_SCALE) {
                $question['min'] = $this->options['min'] ?? 1;
                $question['max'] = $this->options['max'] ?? 5;
                $question['labels'] = $this->options['labels'] ?? null;
            } elseif ($this->question_type === self::TYPE_MULTIPLE_CHOICE) {
                $question['options'] = $this->options['choices'] ?? $this->options;
            }
        }

        if ($this->interpretation_rules) {
            $question['interpretation_rules'] = $this->interpretation_rules;
        }

        if ($this->audio_file_path) {
            $question['audio_file_path'] = $this->audio_file_path;
        }

        return $question;
    }

    /**
     * Get all available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_WELLNESS => 'Wellness & Mental Health',
            self::CATEGORY_ACADEMIC => 'Academic',
            self::CATEGORY_BEHAVIORAL => 'Behavioral',
            self::CATEGORY_SEL => 'Social-Emotional Learning',
            self::CATEGORY_CUSTOM => 'Custom',
        ];
    }

    /**
     * Get all available question types.
     */
    public static function getQuestionTypes(): array
    {
        return [
            self::TYPE_SCALE => 'Rating Scale',
            self::TYPE_MULTIPLE_CHOICE => 'Multiple Choice',
            self::TYPE_TEXT => 'Open Text',
            self::TYPE_VOICE => 'Voice Response',
            self::TYPE_MATRIX => 'Matrix/Grid',
        ];
    }
}
