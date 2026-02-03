<?php

declare(strict_types=1);

namespace App\Services;

class RolePermissions
{
    /**
     * Define which navigation items each role can see.
     * Keys are nav item identifiers, values are arrays of allowed roles.
     */
    public const NAV_PERMISSIONS = [
        // Quick Access Grid
        'home' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner', 'parent'],
        'contacts' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher'],
        'surveys' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner'],
        'dashboards' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner', 'parent'],

        // Workspace Navigation
        'strategy' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'reports' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor'],
        'collect' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'distribute' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'resources' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner', 'parent'],
        'moderation' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'alerts' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher'],
        'marketplace' => ['admin', 'consultant', 'superintendent', 'organization_admin'],

        // Other
        'sub_organizations' => ['admin', 'consultant', 'superintendent'],
        'settings' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher'],
        'messages' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner', 'parent'],

        // Header Actions
        'create_survey' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher'],
        'create_collection' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'create_report' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor'],
        'create_strategy' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
        'create_alert' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor'],
        'create_resource' => ['admin', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher'],
        'create_distribution' => ['admin', 'consultant', 'superintendent', 'organization_admin'],
    ];

    /**
     * Role descriptions for UI display.
     */
    public const ROLE_DESCRIPTIONS = [
        'admin' => 'Full system access',
        'consultant' => 'District consultant with cross-organization visibility',
        'superintendent' => 'District administrator with full oversight',
        'organization_admin' => 'Organization-level administrator (principal)',
        'counselor' => 'Learner support and intervention focus',
        'teacher' => 'Classroom management and learner engagement',
        'learner' => 'Learner portal for surveys and resources',
        'parent' => 'Parent portal for child monitoring',
    ];

    /**
     * Check if a role can access a navigation item.
     */
    public static function canAccess(string $role, string $navItem): bool
    {
        $permissions = self::NAV_PERMISSIONS[$navItem] ?? [];

        return in_array($role, $permissions, true);
    }

    /**
     * Check if the current user (with demo override) can access a nav item.
     */
    public static function currentUserCanAccess(string $navItem): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $effectiveRole = $user->effective_role ?? $user->primary_role;

        return self::canAccess($effectiveRole, $navItem);
    }

    /**
     * Get all navigation items a role can access.
     */
    public static function getAccessibleNavItems(string $role): array
    {
        return array_keys(array_filter(
            self::NAV_PERMISSIONS,
            fn ($allowedRoles) => in_array($role, $allowedRoles, true)
        ));
    }

    /**
     * Check if the current user is in demo mode.
     */
    public static function isInDemoMode(): bool
    {
        $user = auth()->user();

        return $user && $user->isInDemoMode();
    }

    /**
     * Get the effective role for current user.
     */
    public static function getEffectiveRole(): ?string
    {
        $user = auth()->user();

        return $user ? ($user->effective_role ?? $user->primary_role) : null;
    }
}
