<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organization extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'organizations';

    protected $fillable = [
        'org_type',
        'org_name',
        'parent_org_id',
        'ancestor_org_ids',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'address',
        'logo_url',
        'primary_color',
        'secondary_color',
        'timezone',
        'subscription_plan',
        'subscription_status',
        'billing_contact_email',
        'anthropic_api_key',
        'sinch_credentials',
        'enabled_features',
        'cascade_surveys',
        'cascade_resources',
        'cascade_reports',
        'active',
        'created_by',
    ];

    protected $casts = [
        'ancestor_org_ids' => 'array',
        'address' => 'array',
        'sinch_credentials' => 'array',
        'enabled_features' => 'array',
        'cascade_surveys' => 'boolean',
        'cascade_resources' => 'boolean',
        'cascade_reports' => 'boolean',
        'active' => 'boolean',
    ];

    protected $hidden = [
        'anthropic_api_key',
        'sinch_credentials',
    ];

    /**
     * Get the parent organization.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_org_id');
    }

    /**
     * Get child organizations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_org_id');
    }

    /**
     * Get all users in this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'org_id');
    }

    /**
     * Get all students in this organization.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'org_id');
    }

    /**
     * Get all surveys in this organization.
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'org_id');
    }

    /**
     * Get all descendant organization IDs (including self).
     */
    public function getDescendantIdsAttribute(): array
    {
        $ids = [$this->_id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendant_ids);
        }

        return $ids;
    }

    /**
     * Check if this org is an ancestor of another org.
     */
    public function isAncestorOf(Organization $org): bool
    {
        return in_array($this->_id, $org->ancestor_org_ids ?? []);
    }

    /**
     * Get the full hierarchy path.
     */
    public function getHierarchyPathAttribute(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, [
                'id' => $current->_id,
                'name' => $current->org_name,
                'type' => $current->org_type,
            ]);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * Scope to filter by org type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('org_type', $type);
    }

    /**
     * Scope to filter active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
