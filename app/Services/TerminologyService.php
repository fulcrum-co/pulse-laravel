<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrganizationSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TerminologyService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Get a terminology label for the current or specified organization.
     */
    public function get(string $key, ?int $orgId = null): string
    {
        $orgId = $orgId ?? $this->getCurrentOrgId();

        if (!$orgId) {
            return OrganizationSettings::DEFAULT_TERMINOLOGY[$key]
                ?? ucfirst(str_replace('_', ' ', $key));
        }

        return $this->getTerminology($orgId)[$key]
            ?? OrganizationSettings::DEFAULT_TERMINOLOGY[$key]
            ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get the plural form of a terminology label.
     * Convenience method that appends '_plural' to singular keys.
     */
    public function plural(string $singularKey, ?int $orgId = null): string
    {
        // If key already ends in _plural, use it directly
        if (str_ends_with($singularKey, '_plural')) {
            return $this->get($singularKey, $orgId);
        }

        // If key ends in _singular, replace with _plural
        if (str_ends_with($singularKey, '_singular')) {
            $pluralKey = str_replace('_singular', '_plural', $singularKey);
            return $this->get($pluralKey, $orgId);
        }

        // Otherwise, try appending _plural
        return $this->get($singularKey . '_plural', $orgId);
    }

    /**
     * Get all terminology for an organization.
     */
    public function all(?int $orgId = null): array
    {
        $orgId = $orgId ?? $this->getCurrentOrgId();

        if (!$orgId) {
            return OrganizationSettings::DEFAULT_TERMINOLOGY;
        }

        return array_merge(
            OrganizationSettings::DEFAULT_TERMINOLOGY,
            $this->getTerminology($orgId)
        );
    }

    /**
     * Get terminology with caching.
     */
    protected function getTerminology(int $orgId): array
    {
        $cacheKey = "org_terminology_{$orgId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($orgId) {
            $settings = OrganizationSettings::where('org_id', $orgId)->first();
            return $settings?->terminology ?? [];
        });
    }

    /**
     * Clear terminology cache for an organization.
     */
    public function clearCache(int $orgId): void
    {
        Cache::forget("org_terminology_{$orgId}");
    }

    /**
     * Get the current user's organization ID.
     */
    protected function getCurrentOrgId(): ?int
    {
        $user = Auth::user();
        return $user?->org_id;
    }

    /**
     * Replace terminology placeholders in a string.
     * Placeholders format: :term_key (e.g., :course_singular, :learner_plural)
     */
    public function replace(string $text, ?int $orgId = null): string
    {
        $terminology = $this->all($orgId);

        foreach ($terminology as $key => $value) {
            $text = str_replace(":{$key}", $value, $text);
        }

        return $text;
    }

    /**
     * Get terminology grouped by category for admin UI.
     */
    public function getGrouped(?int $orgId = null): array
    {
        $terminology = $this->all($orgId);
        $categories = OrganizationSettings::getTerminologyCategories();
        $grouped = [];

        foreach ($categories as $category => $keys) {
            $grouped[$category] = [];
            foreach ($keys as $key) {
                $grouped[$category][$key] = [
                    'key' => $key,
                    'value' => $terminology[$key] ?? '',
                    'default' => OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? '',
                ];
            }
        }

        return $grouped;
    }
}
