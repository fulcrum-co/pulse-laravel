<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContentTag extends Model
{
    // Tag categories
    public const CATEGORY_TOPIC = 'topic';

    public const CATEGORY_SKILL = 'skill';

    public const CATEGORY_GRADE = 'level';

    public const CATEGORY_SUBJECT = 'subject';

    public const CATEGORY_RISK_FACTOR = 'risk_factor';

    protected $fillable = [
        'org_id',
        'name',
        'slug',
        'category',
        'description',
        'color',
    ];

    protected $casts = [];

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_TOPIC => 'Topic',
            self::CATEGORY_SKILL => 'Skill',
            self::CATEGORY_GRADE => 'Level Level',
            self::CATEGORY_SUBJECT => 'Subject Area',
            self::CATEGORY_RISK_FACTOR => 'Risk Factor',
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
     * Content blocks with this tag.
     */
    public function contentBlocks(): BelongsToMany
    {
        return $this->belongsToMany(ContentBlock::class, 'content_block_tag');
    }

    /**
     * Scope by category.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope for global tags (org_id is null).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('org_id');
    }

    /**
     * Scope for available tags (org-specific or global).
     */
    public function scopeAvailableFor(Builder $query, int $orgId): Builder
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('org_id', $orgId)
                ->orWhereNull('org_id');
        });
    }

    /**
     * Find or create a tag by slug.
     */
    public static function findOrCreateBySlug(string $slug, string $category, ?int $orgId = null, ?string $name = null): self
    {
        return static::firstOrCreate(
            [
                'slug' => $slug,
                'org_id' => $orgId,
            ],
            [
                'name' => $name ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                'category' => $category,
            ]
        );
    }

    /**
     * Get content block count.
     */
    public function getBlockCountAttribute(): int
    {
        return $this->contentBlocks()->count();
    }
}
