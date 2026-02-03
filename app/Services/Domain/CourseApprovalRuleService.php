<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Organization;

class CourseApprovalRuleService
{
    /**
     * Get organization approval settings.
     */
    public function getOrgSettings(int $orgId): array
    {
        $org = Organization::find($orgId);

        if (!$org) {
            return $this->getDefaultSettings();
        }

        $settings = $org->settings ?? [];

        return $settings['ai_course_settings'] ?? $this->getDefaultSettings();
    }

    /**
     * Get default approval settings.
     */
    public function getDefaultSettings(): array
    {
        return [
            'approval_mode' => 'create_approve',
            'auto_generate_enabled' => false,
            'generation_triggers' => ['manual'],
            'notification_recipients' => ['admin'],
            'max_auto_courses_per_day' => 10,
            'require_review_for_ai_generated' => true,
        ];
    }

    /**
     * Check if course should auto-activate.
     */
    public function shouldAutoActivate(int $orgId): bool
    {
        $settings = $this->getOrgSettings($orgId);
        $mode = $settings['approval_mode'] ?? 'create_approve';

        return $mode === 'auto_activate';
    }

    /**
     * Get approval mode for organization.
     */
    public function getApprovalMode(int $orgId): string
    {
        $settings = $this->getOrgSettings($orgId);

        return $settings['approval_mode'] ?? 'create_approve';
    }
}
