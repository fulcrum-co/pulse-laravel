<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
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
     * Get all assignments for this resource.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
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
}
