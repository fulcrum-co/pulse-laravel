<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_type',
        'org_name',
        'parent_org_id',
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
        'active',
        'created_by',
    ];

    protected $casts = [
        'address' => 'array',
        'active' => 'boolean',
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
     * Get all strategic plans for this organization.
     */
    public function strategicPlans(): HasMany
    {
        return $this->hasMany(StrategicPlan::class, 'org_id');
    }

    /**
     * Get organization settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(OrganizationSettings::class, 'org_id');
    }

    /**
     * Get or create organization settings.
     */
    public function getOrCreateSettings(): OrganizationSettings
    {
        return OrganizationSettings::forOrganization($this->id);
    }

    /**
     * Check if this org can push content to another org.
     */
    public function canPushContentTo(Organization $targetOrg): bool
    {
        // Can push to direct children
        if ($targetOrg->parent_org_id === $this->id) {
            return true;
        }

        // Check if target is a descendant
        $current = $targetOrg;
        while ($current->parent_org_id) {
            if ($current->parent_org_id === $this->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Get all downstream (child) organizations recursively.
     */
    public function getDownstreamOrganizations(): \Illuminate\Support\Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDownstreamOrganizations());
        }

        return $descendants;
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
