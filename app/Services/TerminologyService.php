<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrganizationSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TerminologyService
{
    /**
     * Default terminology organized by application section.
     */
    public const DEFAULTS = [
        // === CONTACTS SECTION ===
        'contact' => 'Contact',
        'contacts' => 'Contacts',
        'student' => 'Student',
        'students' => 'Students',
        'participant' => 'Participant',
        'participants' => 'Participants',
        'counselor' => 'Counselor',
        'counselors' => 'Counselors',
        'advisor' => 'Advisor',
        'advisors' => 'Advisors',
        'teacher' => 'Teacher',
        'teachers' => 'Teachers',
        'staff' => 'Staff',
        'guardian' => 'Guardian',
        'guardians' => 'Guardians',
        'parent' => 'Parent',
        'parents' => 'Parents',

        // === ACADEMIC / ORGANIZATIONAL ===
        'grade' => 'Grade',
        'grade_level' => 'Grade Level',
        'grades' => 'Grades',
        'class' => 'Class',
        'classes' => 'Classes',
        'cohort' => 'Cohort',
        'cohorts' => 'Cohorts',
        'group' => 'Group',
        'groups' => 'Groups',
        'school' => 'School',
        'schools' => 'Schools',
        'building' => 'Building',
        'buildings' => 'Buildings',
        'department' => 'Department',
        'departments' => 'Departments',
        'organization' => 'Organization',

        // === RISK & STATUS ===
        'risk_level' => 'Risk Level',
        'at_risk' => 'At Risk',
        'high_risk' => 'High Risk',
        'low_risk' => 'Low Risk',
        'good_standing' => 'Good Standing',
        'on_track' => 'On Track',
        'off_track' => 'Off Track',
        'needs_support' => 'Needs Support',
        'intervention' => 'Intervention',
        'interventions' => 'Interventions',

        // === DATA COLLECTION ===
        'survey' => 'Survey',
        'surveys' => 'Surveys',
        'assessment' => 'Assessment',
        'assessments' => 'Assessments',
        'check_in' => 'Check-In',
        'check_ins' => 'Check-Ins',
        'response' => 'Response',
        'responses' => 'Responses',
        'submission' => 'Submission',
        'submissions' => 'Submissions',

        // === RESOURCES & COURSES ===
        'resource' => 'Resource',
        'resources' => 'Resources',
        'course' => 'Course',
        'courses' => 'Courses',
        'module' => 'Module',
        'modules' => 'Modules',
        'lesson' => 'Lesson',
        'lessons' => 'Lessons',
        'assignment' => 'Assignment',
        'assignments' => 'Assignments',

        // === COMMUNICATION ===
        'message' => 'Message',
        'messages' => 'Messages',
        'notification' => 'Notification',
        'notifications' => 'Notifications',
        'announcement' => 'Announcement',
        'announcements' => 'Announcements',

        // === REPORTS ===
        'report' => 'Report',
        'reports' => 'Reports',
        'dashboard' => 'Dashboard',
        'analytics' => 'Analytics',
        'metrics' => 'Metrics',
    ];

    /**
     * Terminology categories for the admin UI, organized by application section.
     */
    public const CATEGORIES = [
        'Contacts & People' => [
            'contact', 'contacts', 'student', 'students', 'participant', 'participants',
            'counselor', 'counselors', 'advisor', 'advisors', 'teacher', 'teachers',
            'staff', 'guardian', 'guardians', 'parent', 'parents',
        ],
        'Organization & Structure' => [
            'grade', 'grade_level', 'grades', 'class', 'classes', 'cohort', 'cohorts',
            'group', 'groups', 'school', 'schools', 'building', 'buildings',
            'department', 'departments', 'organization',
        ],
        'Risk & Status' => [
            'risk_level', 'at_risk', 'high_risk', 'low_risk', 'good_standing',
            'on_track', 'off_track', 'needs_support', 'intervention', 'interventions',
        ],
        'Data Collection' => [
            'survey', 'surveys', 'assessment', 'assessments', 'check_in', 'check_ins',
            'response', 'responses', 'submission', 'submissions',
        ],
        'Resources & Learning' => [
            'resource', 'resources', 'course', 'courses', 'module', 'modules',
            'lesson', 'lessons', 'assignment', 'assignments',
        ],
        'Communication' => [
            'message', 'messages', 'notification', 'notifications',
            'announcement', 'announcements',
        ],
        'Reports & Analytics' => [
            'report', 'reports', 'dashboard', 'analytics', 'metrics',
        ],
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
