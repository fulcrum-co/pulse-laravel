<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'departments';

    protected $fillable = [
        'org_id',
        'parent_department_id',
        'name',
        'description',
        'department_head_id',
        'strategy_ids',
        'instructor_ids',
        'learner_count',
        'active',
    ];

    protected $casts = [
        'strategy_ids' => 'array',
        'instructor_ids' => 'array',
        'learner_count' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get the organization (organization).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the direct_supervisor department.
     */
    public function direct_supervisor(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Get child departments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /**
     * Get the department head.
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    /**
     * Get learning_groups in this department.
     */
    public function learning_groups(): HasMany
    {
        return $this->hasMany(LearningGroup::class, 'department_id');
    }

    /**
     * Scope to filter active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
