<?php

namespace App\Traits;

use Laravel\Scout\Searchable as ScoutSearchable;

/**
 * Searchable trait for models that should be indexed in Meilisearch.
 *
 * This trait extends Laravel Scout's Searchable trait with organization-scoped
 * search capabilities for multi-tenant applications.
 */
trait Searchable
{
    use ScoutSearchable;

    /**
     * Get the index name for the model.
     * Prefixed with 'pulse_' via scout config.
     */
    public function searchableAs(): string
    {
        return config('scout.prefix').$this->getTable();
    }

    /**
     * Determine if the model should be searchable.
     * Only index active, non-deleted records.
     */
    public function shouldBeSearchable(): bool
    {
        // Don't index soft-deleted records
        if (method_exists($this, 'trashed') && $this->trashed()) {
            return false;
        }

        // Check for common "active" or "status" fields
        if (isset($this->is_active)) {
            return (bool) $this->is_active;
        }

        if (isset($this->status) && in_array($this->status, ['draft', 'archived', 'deleted'])) {
            return false;
        }

        return true;
    }

    /**
     * Get the value used to index the model.
     * Override in individual models for custom data.
     */
    public function toSearchableArray(): array
    {
        // Start with basic fields that most models have
        $searchable = [
            'id' => $this->getKey(),
        ];

        // Add organization ID for multi-tenant filtering
        if (isset($this->org_id)) {
            $searchable['org_id'] = $this->org_id;
        }

        // Add common fields if they exist
        $commonFields = ['title', 'name', 'description', 'status', 'created_at', 'updated_at'];
        foreach ($commonFields as $field) {
            if (isset($this->{$field})) {
                $value = $this->{$field};

                // Convert dates to timestamps for sorting
                if ($value instanceof \DateTimeInterface) {
                    $searchable[$field] = $value->getTimestamp();
                } else {
                    $searchable[$field] = $value;
                }
            }
        }

        return $searchable;
    }

    /**
     * Scope search results to the current organization.
     */
    public static function searchForOrganization(string $query, int $orgId): \Laravel\Scout\Builder
    {
        return static::search($query)->where('org_id', $orgId);
    }

    /**
     * Scope search results to multiple organizations.
     */
    public static function searchForOrganizations(string $query, array $orgIds): \Laravel\Scout\Builder
    {
        return static::search($query)->whereIn('org_id', $orgIds);
    }
}
