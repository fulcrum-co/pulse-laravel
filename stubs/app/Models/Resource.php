<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Resource extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'resources';

    protected $fillable = [
        'org_id',
        'created_by_org_type',
        'cascaded_from_org_id',
        'accessible_to_org_ids',
        'title',
        'description',
        'resource_type',
        'file_url',
        'external_url',
        'embedded_content',
        'tags',
        'course_lessons',
        'approval_status',
        'approved_by',
        'approval_date',
        'view_count',
        'assignment_count',
        'completion_count',
        'avg_rating',
        'active',
        'featured',
        'created_by',
    ];

    protected $casts = [
        'accessible_to_org_ids' => 'array',
        'tags' => 'array',
        'course_lessons' => 'array',
        'approval_date' => 'datetime',
        'view_count' => 'integer',
        'assignment_count' => 'integer',
        'completion_count' => 'integer',
        'avg_rating' => 'float',
        'active' => 'boolean',
        'featured' => 'boolean',
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
     * Get the user who approved this resource.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all assignments for this resource.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class, 'resource_id');
    }

    /**
     * Increment view count.
     */
    public function recordView(): void
    {
        $this->increment('view_count');
    }

    /**
     * Scope to filter approved resources.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope to filter active resources.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter featured resources.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope to filter by resource type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('resource_type', $type);
    }

    /**
     * Scope to filter by subject domain.
     */
    public function scopeForSubject($query, string $subject)
    {
        return $query->where('tags.subject_domain', $subject);
    }

    /**
     * Scope to filter by grade level.
     */
    public function scopeForGradeLevel($query, string $grade)
    {
        return $query->where('tags.grade_level', $grade);
    }

    /**
     * Scope to filter by performance trigger.
     */
    public function scopeForTrigger($query, string $trigger)
    {
        return $query->where('tags.performance_trigger', $trigger);
    }

    /**
     * Scope to filter resources accessible to an org.
     */
    public function scopeAccessibleTo($query, string $orgId)
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('org_id', $orgId)
                ->orWhere('accessible_to_org_ids', $orgId);
        });
    }
}
