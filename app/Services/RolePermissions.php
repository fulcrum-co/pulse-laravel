<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class RolePermissions
{
    /**
     * Define which navigation items each role can see.
     * Keys are nav item identifiers, values are arrays of allowed roles.
     */
    public const NAV_PERMISSIONS = [
        // Quick Access Grid
        'home' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant', 'direct_supervisor'],
        'contacts' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor'],
        'surveys' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant'],
        'dashboards' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant', 'direct_supervisor'],

        // Workspace Navigation
        'strategy' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'reports' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person'],
        'collect' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'distribute' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'resources' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant', 'direct_supervisor'],
        'moderation' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'alerts' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor'],
        'marketplace' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],

        // Other
        'sub_organizations' => ['admin', 'consultant', 'administrative_role'],
        'settings' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor'],
        'messages' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant', 'direct_supervisor'],

        // Header Actions
        'create_survey' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor'],
        'create_collection' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'create_report' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person'],
        'create_strategy' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
        'create_alert' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person'],
        'create_resource' => ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor'],
        'create_distribution' => ['admin', 'consultant', 'administrative_role', 'organization_admin'],
    ];

    /**
     * Role descriptions for UI display.
     */
    public static function getRoleDescriptions(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            'admin' => $terminology->get('role_description_admin_label'),
            'consultant' => $terminology->get('role_description_consultant_label'),
            'administrative_role' => $terminology->get('role_description_administrative_label'),
            'organization_admin' => $terminology->get('role_description_organization_admin_label'),
            'support_person' => $terminology->get('role_description_support_person_label'),
            'instructor' => $terminology->get('role_description_instructor_label'),
            'participant' => $terminology->get('role_description_participant_label'),
            'direct_supervisor' => $terminology->get('role_description_direct_supervisor_label'),
        ];
    }

    /**
     * Check if a role can access a navigation item.
     */
    public static function canAccess(string $role, string $navItem): bool
    {
        $permissions = self::NAV_PERMISSIONS[$navItem] ?? [];

        return in_array(User::normalizeRole($role), $permissions, true);
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
            fn ($allowedRoles) => in_array(User::normalizeRole($role), $allowedRoles, true)
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
