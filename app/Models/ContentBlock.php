<?php

namespace App\Models;

use App\Traits\HasContentModeration;
use App\Traits\HasEmbedding;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ContentBlock extends Model
{
    use SoftDeletes, Searchable, HasEmbedding, HasContentModeration;

    // Block types
    public const TYPE_VIDEO = 'video';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_ASSESSMENT = 'assessment';
    public const TYPE_TEXT = 'text';
    public const TYPE_LINK = 'link';
    public const TYPE_EMBED = 'embed';

    // Source types
    public const SOURCE_INTERNAL = 'internal';
    public const SOURCE_YOUTUBE = 'youtube';
    public const SOURCE_VIMEO = 'vimeo';
    public const SOURCE_KHAN_ACADEMY = 'khan_academy';
    public const SOURCE_UPLOADED = 'uploaded';
    public const SOURCE_CUSTOM_URL = 'custom_url';

    // Statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'org_id',
        'title',
        'slug',
        'description',
        'block_type',
        'content_data',
        'source_type',
        'source_url',
        'source_metadata',
        'topics',
        'skills',
        'grade_levels',
        'subject_areas',
        'target_risk_factors',
        'target_demographics',
        'iep_appropriate',
        'language',
        'usage_count',
        'avg_completion_rate',
        'avg_rating',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'content_data' => 'array',
        'source_metadata' => 'array',
        'topics' => 'array',
        'skills' => 'array',
        'grade_levels' => 'array',
        'subject_areas' => 'array',
        'target_risk_factors' => 'array',
        'target_demographics' => 'array',
        'iep_appropriate' => 'boolean',
        'avg_completion_rate' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'source_type' => self::SOURCE_INTERNAL,
        'iep_appropriate' => true,
        'language' => 'en',
        'usage_count' => 0,
    ];

    /**
     * Get available block types.
     */
    public static function getBlockTypes(): array
    {
        return [
            self::TYPE_VIDEO => 'Video',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_ACTIVITY => 'Activity',
            self::TYPE_ASSESSMENT => 'Assessment',
            self::TYPE_TEXT => 'Text Content',
            self::TYPE_LINK => 'External Link',
            self::TYPE_EMBED => 'Embedded Content',
        ];
    }

    /**
     * Get available source types.
     */
    public static function getSourceTypes(): array
    {
        return [
            self::SOURCE_INTERNAL => 'Internal',
            self::SOURCE_YOUTUBE => 'YouTube',
            self::SOURCE_VIMEO => 'Vimeo',
            self::SOURCE_KHAN_ACADEMY => 'Khan Academy',
            self::SOURCE_UPLOADED => 'Uploaded File',
            self::SOURCE_CUSTOM_URL => 'Custom URL',
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
     * Reviewer relationship.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Tags relationship.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContentTag::class, 'content_block_tag');
    }

    /**
     * Scope to active blocks.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope by block type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('block_type', $type);
    }

    /**
     * Scope by source type.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source_type', $source);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope for global/system blocks (org_id is null).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('org_id');
    }

    /**
     * Scope for available blocks (org-specific or global).
     */
    public function scopeAvailableFor(Builder $query, int $orgId): Builder
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('org_id', $orgId)
              ->orWhereNull('org_id');
        });
    }

    /**
     * Scope by topics.
     */
    public function scopeForTopics(Builder $query, array $topics): Builder
    {
        return $query->where(function ($q) use ($topics) {
            foreach ($topics as $topic) {
                $q->orWhereJsonContains('topics', $topic);
            }
        });
    }

    /**
     * Scope by skills.
     */
    public function scopeForSkills(Builder $query, array $skills): Builder
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('skills', $skill);
            }
        });
    }

    /**
     * Scope by grade levels.
     */
    public function scopeForGradeLevels(Builder $query, array $grades): Builder
    {
        return $query->where(function ($q) use ($grades) {
            foreach ($grades as $grade) {
                $q->orWhereJsonContains('grade_levels', $grade);
            }
        });
    }

    /**
     * Scope by risk factors.
     */
    public function scopeForRiskFactors(Builder $query, array $factors): Builder
    {
        return $query->where(function ($q) use ($factors) {
            foreach ($factors as $factor) {
                $q->orWhereJsonContains('target_risk_factors', $factor);
            }
        });
    }

    /**
     * Scope to IEP-appropriate content.
     */
    public function scopeIepAppropriate(Builder $query): Builder
    {
        return $query->where('iep_appropriate', true);
    }

    /**
     * Scope by language.
     */
    public function scopeInLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Update average rating.
     */
    public function updateRating(float $newRating): void
    {
        if ($this->avg_rating === null) {
            $this->update(['avg_rating' => $newRating]);
        } else {
            // Simple moving average (you may want a more sophisticated approach)
            $this->update([
                'avg_rating' => ($this->avg_rating + $newRating) / 2
            ]);
        }
    }

    /**
     * Check if block is from external source.
     */
    public function isExternal(): bool
    {
        return in_array($this->source_type, [
            self::SOURCE_YOUTUBE,
            self::SOURCE_VIMEO,
            self::SOURCE_KHAN_ACADEMY,
            self::SOURCE_CUSTOM_URL,
        ]);
    }

    /**
     * Check if block is a video.
     */
    public function isVideo(): bool
    {
        return $this->block_type === self::TYPE_VIDEO;
    }

    /**
     * Get video embed URL if applicable.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->isVideo()) {
            return null;
        }

        return $this->content_data['embed_url']
            ?? $this->content_data['video_url']
            ?? $this->source_url;
    }

    /**
     * Get video duration in seconds if available.
     */
    public function getDurationSecondsAttribute(): ?int
    {
        return $this->content_data['duration_seconds']
            ?? $this->source_metadata['duration_seconds']
            ?? null;
    }

    /**
     * Get formatted duration (e.g., "5:30").
     */
    public function getFormattedDurationAttribute(): ?string
    {
        $seconds = $this->duration_seconds;
        if ($seconds === null) {
            return null;
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
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
            'block_type' => $this->block_type,
            'source_type' => $this->source_type,
            'status' => $this->status,
            'topics' => $this->topics ?? [],
            'skills' => $this->skills ?? [],
            'grade_levels' => $this->grade_levels ?? [],
            'subject_areas' => $this->subject_areas ?? [],
            'target_risk_factors' => $this->target_risk_factors ?? [],
            'iep_appropriate' => (bool) $this->iep_appropriate,
            'language' => $this->language ?? 'en',
            'usage_count' => $this->usage_count ?? 0,
            'avg_rating' => $this->avg_rating,
            'created_at' => $this->created_at?->getTimestamp(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return !$this->trashed() && $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the text to be embedded for semantic search.
     */
    public function getEmbeddingText(): string
    {
        $parts = [
            $this->title,
            $this->description,
            $this->block_type,
        ];

        if (!empty($this->topics)) {
            $topics = is_array($this->topics) ? $this->topics : [];
            $parts[] = 'Topics: ' . implode(', ', $topics);
        }

        if (!empty($this->skills)) {
            $skills = is_array($this->skills) ? $this->skills : [];
            $parts[] = 'Skills: ' . implode(', ', $skills);
        }

        if (!empty($this->grade_levels)) {
            $grades = is_array($this->grade_levels) ? $this->grade_levels : [];
            $parts[] = 'Grades: ' . implode(', ', $grades);
        }

        if (!empty($this->subject_areas)) {
            $subjects = is_array($this->subject_areas) ? $this->subject_areas : [];
            $parts[] = 'Subjects: ' . implode(', ', $subjects);
        }

        if (!empty($this->target_risk_factors)) {
            $factors = is_array($this->target_risk_factors) ? $this->target_risk_factors : [];
            $parts[] = 'Risk factors: ' . implode(', ', $factors);
        }

        return implode('. ', array_filter($parts));
    }

    /**
     * Get the fields that contribute to the embedding text.
     */
    protected function getEmbeddingTextFields(): array
    {
        return ['title', 'description', 'topics', 'skills', 'grade_levels', 'subject_areas', 'target_risk_factors'];
    }

    /**
     * Get the content text to be moderated.
     */
    public function getModerationContent(): string
    {
        $parts = [
            "Title: {$this->title}",
            "Description: {$this->description}",
            "Block Type: {$this->block_type}",
        ];

        // Include content data if it's text-based
        if (!empty($this->content_data) && isset($this->content_data['text'])) {
            $parts[] = "Content: {$this->content_data['text']}";
        }

        if (!empty($this->topics)) {
            $topics = is_array($this->topics) ? implode(', ', $this->topics) : $this->topics;
            $parts[] = "Topics: {$topics}";
        }

        if (!empty($this->skills)) {
            $skills = is_array($this->skills) ? implode(', ', $this->skills) : $this->skills;
            $parts[] = "Skills: {$skills}";
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Get context information for moderation.
     */
    public function getModerationContext(): array
    {
        return [
            'type' => 'ContentBlock',
            'id' => $this->id,
            'org_id' => $this->org_id,
            'target_grades' => $this->grade_levels ?? [],
            'block_type' => $this->block_type,
            'is_ai_generated' => false, // Content blocks are typically not AI-generated
        ];
    }

    /**
     * Get the fields that require re-moderation when changed.
     */
    protected function getModerationTextFields(): array
    {
        return ['title', 'description', 'content_data'];
    }
}
