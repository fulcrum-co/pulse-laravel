<?php

namespace App\Models;

use App\Traits\HasEmbedding;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use SoftDeletes, Searchable, HasEmbedding;

    protected $fillable = [
        'org_id',
        'source_resource_id',
        'source_org_id',
        'title',
        'description',
        'resource_type',
        'category',
        'tags',
        'url',
        'file_path',
        'thumbnail_url',
        'estimated_duration_minutes',
        'target_grades',
        'target_risk_levels',
        'is_public',
        'active',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'target_grades' => 'array',
        'target_risk_levels' => 'array',
        'is_public' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the organization that owns this resource.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this resource.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the source resource this was pushed from.
     */
    public function sourceResource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'source_resource_id');
    }

    /**
     * Get the source organization this was pushed from.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get all resources pushed from this resource.
     */
    public function pushedResources(): HasMany
    {
        return $this->hasMany(Resource::class, 'source_resource_id');
    }

    /**
     * Get all assignments for this resource.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
    }

    /**
     * Push this resource to another organization.
     * Creates a copy for the target org.
     */
    public function pushToOrganization(Organization $targetOrg, ?int $pushedBy = null): self
    {
        $newResource = $this->replicate([
            'org_id',
            'source_resource_id',
            'source_org_id',
            'created_by',
        ]);

        $newResource->org_id = $targetOrg->id;
        $newResource->source_resource_id = $this->id;
        $newResource->source_org_id = $this->org_id;
        $newResource->created_by = $pushedBy;
        $newResource->title = $this->title . ' (from ' . $this->organization->org_name . ')';
        $newResource->save();

        return $newResource;
    }

    /**
     * Check if this resource was pushed from another organization.
     */
    public function wasPushed(): bool
    {
        return $this->source_resource_id !== null;
    }

    /**
     * Scope to filter active resources.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by resource type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('resource_type', $type);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
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
            'resource_type' => $this->resource_type,
            'category' => $this->category,
            'tags' => $this->tags ?? [],
            'target_grades' => $this->target_grades ?? [],
            'target_risk_levels' => $this->target_risk_levels ?? [],
            'is_active' => (bool) $this->active,
            'is_public' => (bool) $this->is_public,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'created_at' => $this->created_at?->getTimestamp(),
            'updated_at' => $this->updated_at?->getTimestamp(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return !$this->trashed() && $this->active;
    }

    /**
     * Get the text to be embedded for semantic search.
     */
    public function getEmbeddingText(): string
    {
        $parts = [
            $this->title,
            $this->description,
            $this->category,
            $this->resource_type,
        ];

        if (!empty($this->tags)) {
            $tags = is_array($this->tags) ? $this->tags : [];
            $parts[] = 'Tags: ' . implode(', ', $tags);
        }

        if (!empty($this->target_grades)) {
            $grades = is_array($this->target_grades) ? $this->target_grades : [];
            $parts[] = 'Grades: ' . implode(', ', $grades);
        }

        if (!empty($this->target_risk_levels)) {
            $levels = is_array($this->target_risk_levels) ? $this->target_risk_levels : [];
            $parts[] = 'Risk levels: ' . implode(', ', $levels);
        }

        return implode('. ', array_filter($parts));
    }

    /**
     * Get the fields that contribute to the embedding text.
     */
    protected function getEmbeddingTextFields(): array
    {
        return ['title', 'description', 'category', 'resource_type', 'tags', 'target_grades', 'target_risk_levels'];
    }
}
