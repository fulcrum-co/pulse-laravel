<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'category_id',
        'created_by',
        'slug',
        'title',
        'content',
        'excerpt',
        'target_roles',
        'search_keywords',
        'video_url',
        'view_count',
        'helpful_count',
        'not_helpful_count',
        'is_published',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'search_keywords' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
    ];

    /**
     * Get the category this article belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'category_id');
    }

    /**
     * Get the organization that owns this article.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this article.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by organization (includes system-wide articles).
     */
    public function scopeForOrganization($query, ?int $orgId)
    {
        return $query->where(function ($q) use ($orgId) {
            $q->whereNull('org_id'); // System-wide articles
            if ($orgId) {
                $q->orWhere('org_id', $orgId); // Org-specific articles
            }
        });
    }

    /**
     * Scope to get published articles only.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope to get featured articles.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Record feedback (helpful/not helpful).
     */
    public function recordFeedback(bool $helpful): void
    {
        if ($helpful) {
            $this->increment('helpful_count');
        } else {
            $this->increment('not_helpful_count');
        }
    }

    /**
     * Get estimated reading time in minutes.
     */
    public function getReadingTimeMinutes(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $readingTime = ceil($wordCount / 200); // Average reading speed

        return max(1, (int) $readingTime);
    }
}
