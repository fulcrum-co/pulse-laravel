<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrganizationSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TerminologyService
{
    /**
     * Default terminology - matches current hardcoded values.
     * Expand this list as needed.
     */
    public const DEFAULTS = [
        // Core entities
        'student' => 'Student',
        'students' => 'Students',
        'grade' => 'Grade',
        'grade_level' => 'Grade Level',
        'teacher' => 'Teacher',
        'teachers' => 'Teachers',
        'school' => 'School',
        'schools' => 'Schools',

        // Common labels
        'at_risk' => 'At Risk',
        'high_risk' => 'High Risk',
        'low_risk' => 'Low Risk',
        'good_standing' => 'Good Standing',
    ];

    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Get a terminology label for the current organization.
     */
    public function get(string $key): string
    {
        $custom = $this->getCustomTerms();

        return $custom[$key] ?? self::DEFAULTS[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get all terminology (merged custom + defaults).
     */
    public function all(): array
    {
        return array_merge(self::DEFAULTS, $this->getCustomTerms());
    }

    /**
     * Get custom terms for current user's organization.
     */
    protected function getCustomTerms(): array
    {
        $user = Auth::user();
        if (! $user?->org_id) {
            return [];
        }

        $cacheKey = "org_terminology_{$user->org_id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->loadTermsFromDb($user->org_id);
        });
    }

    /**
     * Load terminology from database.
     */
    protected function loadTermsFromDb(int $orgId): array
    {
        $settings = OrganizationSettings::where('org_id', $orgId)->first();

        return $settings?->getSetting('terminology', []) ?? [];
    }

    /**
     * Clear cached terminology for an organization.
     */
    public function clearCache(int $orgId): void
    {
        Cache::forget("org_terminology_{$orgId}");
    }

    /**
     * Get available terminology keys for admin UI.
     */
    public static function getAvailableKeys(): array
    {
        return array_keys(self::DEFAULTS);
    }
}
