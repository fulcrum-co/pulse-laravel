<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class AlertDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $school = Organization::first();
        }
        if (! $school) {
            $this->command->error('No organization found. Please seed organizations first.');

            return;
        }

        $users = User::where('org_id', $school->id)->limit(5)->get();
        if ($users->isEmpty()) {
            $users = User::limit(5)->get();
        }
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please seed users first.');

            return;
        }

        $primaryUser = $users->first();
        $admin = User::where('org_id', $school->id)
            ->whereIn('primary_role', ['admin', 'school_admin', 'consultant'])
            ->first() ?? $primaryUser;

        $this->command->info('Creating demo alerts/notifications...');

        $notifications = [
            // ===== WORKFLOW ALERTS (urgent/high priority) =====
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'risk_level_change',
                'title' => 'Student Risk Level Elevated',
                'body' => 'Marcus Johnson\'s risk level has changed from Moderate to High based on recent attendance and grade data. Immediate review recommended.',
                'icon' => 'exclamation-triangle',
                'priority' => UserNotification::PRIORITY_URGENT,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/students',
                'action_label' => 'View Student Profile',
            ],
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'attendance_threshold',
                'title' => 'Chronic Absenteeism Alert',
                'body' => '3 students in your caseload have crossed the 10% absence threshold this semester: Aaliyah Davis, Carlos Mendez, and Tyler Brooks.',
                'icon' => 'bell-alert',
                'priority' => UserNotification::PRIORITY_HIGH,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/reports',
                'action_label' => 'View Attendance Report',
            ],
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'intervention_needed',
                'title' => 'Intervention Follow-up Overdue',
                'body' => 'The behavioral intervention plan for Sophia Chen was due for follow-up 3 days ago. Please update the plan status.',
                'icon' => 'clock',
                'priority' => UserNotification::PRIORITY_HIGH,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/strategies',
                'action_label' => 'Review Plan',
            ],
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'grade_drop',
                'title' => 'Significant Grade Drop Detected',
                'body' => 'Emma Rodriguez dropped from a B+ to a D in Algebra II over the last 3 weeks. This pattern matches previous intervention triggers.',
                'icon' => 'arrow-trending-down',
                'priority' => UserNotification::PRIORITY_URGENT,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/students',
                'action_label' => 'View Academic History',
            ],

            // ===== SURVEY NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_SURVEY,
                'type' => 'survey_completed',
                'title' => 'Student Wellness Survey Complete',
                'body' => 'The Q2 Student Wellness Check-In survey has been completed by 87% of students (312/358). Results are ready for review.',
                'icon' => 'clipboard-document-check',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/surveys',
                'action_label' => 'View Survey Results',
            ],
            [
                'category' => UserNotification::CATEGORY_SURVEY,
                'type' => 'survey_response_flagged',
                'title' => 'Survey Response Requires Attention',
                'body' => 'A student response on the Social-Emotional Screening has been flagged for follow-up. The response indicates potential need for counseling support.',
                'icon' => 'flag',
                'priority' => UserNotification::PRIORITY_HIGH,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/surveys',
                'action_label' => 'Review Flagged Response',
            ],
            [
                'category' => UserNotification::CATEGORY_SURVEY,
                'type' => 'survey_reminder',
                'title' => 'Teacher Climate Survey Closing Soon',
                'body' => 'The Staff Climate & Culture survey closes in 3 days. Current response rate is 62% (45/73 staff members).',
                'icon' => 'clock',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_READ,
                'action_url' => '/surveys',
                'action_label' => 'Send Reminder',
            ],

            // ===== REPORT NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_REPORT,
                'type' => 'report_generated',
                'title' => 'Monthly Attendance Report Ready',
                'body' => 'Your January 2026 attendance report has been generated. School-wide attendance rate: 94.2% (up 1.3% from December).',
                'icon' => 'chart-bar',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/reports',
                'action_label' => 'View Report',
            ],
            [
                'category' => UserNotification::CATEGORY_REPORT,
                'type' => 'report_shared',
                'title' => 'Equity Audit Report Shared With You',
                'body' => 'Dr. Sarah Mitchell shared the "Discipline Data Equity Audit" report with you. 3 data points require your review.',
                'icon' => 'share',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_READ,
                'action_url' => '/reports',
                'action_label' => 'Open Report',
            ],

            // ===== STRATEGY/PLAN NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_STRATEGY,
                'type' => 'plan_milestone',
                'title' => 'IEP Annual Review Due in 14 Days',
                'body' => 'The annual IEP review for James Wilson is due on February 22, 2026. All team members have been notified.',
                'icon' => 'calendar',
                'priority' => UserNotification::PRIORITY_HIGH,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/strategies',
                'action_label' => 'View IEP Details',
            ],
            [
                'category' => UserNotification::CATEGORY_STRATEGY,
                'type' => 'plan_progress',
                'title' => 'Behavior Plan Goal Met',
                'body' => 'Jayden Miller has met the behavioral goal "Reduce office referrals to 1 per month" for 3 consecutive months. Consider updating the plan.',
                'icon' => 'trophy',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/strategies',
                'action_label' => 'Review Progress',
            ],

            // ===== COURSE NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_COURSE,
                'type' => 'course_generated',
                'title' => 'New AI Course Ready for Review',
                'body' => 'A new mini-course "Managing Test Anxiety" has been auto-generated based on recent student wellness signals. It needs your approval before publishing.',
                'icon' => 'sparkles',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/admin/moderation?queueType=courses',
                'action_label' => 'Review Course',
            ],
            [
                'category' => UserNotification::CATEGORY_COURSE,
                'type' => 'course_completion',
                'title' => '5 Students Completed "Growth Mindset"',
                'body' => '5 students in your advisory group completed the Growth Mindset Workshop course this week. Average reflection score: 4.2/5.',
                'icon' => 'academic-cap',
                'priority' => UserNotification::PRIORITY_LOW,
                'status' => UserNotification::STATUS_READ,
                'action_url' => '/learning',
                'action_label' => 'View Completions',
            ],

            // ===== COLLECTION NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_COLLECTION,
                'type' => 'collection_due',
                'title' => 'Data Collection Due Tomorrow',
                'body' => 'The "Monthly Behavior Tracking" data collection window closes tomorrow. 4 of 12 teachers have not yet submitted entries.',
                'icon' => 'circle-stack',
                'priority' => UserNotification::PRIORITY_HIGH,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/collections',
                'action_label' => 'View Collection',
            ],
            [
                'category' => UserNotification::CATEGORY_COLLECTION,
                'type' => 'collection_complete',
                'title' => 'Reading Assessment Data Imported',
                'body' => 'Spring DIBELS assessment data has been successfully imported for 342 students. 28 students flagged as below benchmark.',
                'icon' => 'arrow-down-tray',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/collections',
                'action_label' => 'Review Data',
            ],

            // ===== SYSTEM NOTIFICATIONS =====
            [
                'category' => UserNotification::CATEGORY_SYSTEM,
                'type' => 'system_update',
                'title' => 'New Feature: Course Approval Workflow',
                'body' => 'AI-generated courses now require approval before being assigned to students. Visit the moderation queue to review pending courses.',
                'icon' => 'sparkles',
                'priority' => UserNotification::PRIORITY_LOW,
                'status' => UserNotification::STATUS_READ,
                'action_url' => '/admin/moderation',
                'action_label' => 'Learn More',
            ],
            [
                'category' => UserNotification::CATEGORY_SYSTEM,
                'type' => 'maintenance',
                'title' => 'Scheduled Maintenance Tonight',
                'body' => 'The platform will undergo maintenance tonight from 11 PM - 2 AM EST. Some features may be temporarily unavailable.',
                'icon' => 'wrench-screwdriver',
                'priority' => UserNotification::PRIORITY_LOW,
                'status' => UserNotification::STATUS_DISMISSED,
                'action_url' => null,
                'action_label' => null,
            ],

            // ===== MORE WORKFLOW ALERTS FOR VARIETY =====
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'peer_concern',
                'title' => 'Peer Concern Report Filed',
                'body' => 'A student submitted an anonymous concern about a classmate\'s well-being through the wellness check-in. Review needed within 24 hours.',
                'icon' => 'user-group',
                'priority' => UserNotification::PRIORITY_URGENT,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/students',
                'action_label' => 'Review Concern',
            ],
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'provider_match',
                'title' => 'New Provider Match Available',
                'body' => 'Based on current caseload needs, Dr. Aisha Patel (BCBA) has availability for 3 new students requiring behavioral support.',
                'icon' => 'user-plus',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_UNREAD,
                'action_url' => '/marketplace/providers',
                'action_label' => 'View Provider',
            ],

            // ===== SNOOZED NOTIFICATION =====
            [
                'category' => UserNotification::CATEGORY_STRATEGY,
                'type' => 'plan_review',
                'title' => '504 Plan Review Scheduled',
                'body' => 'Reminder: The 504 plan review meeting for Ashley Park is scheduled for next Monday at 2:00 PM in the conference room.',
                'icon' => 'calendar',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_SNOOZED,
                'action_url' => '/strategies',
                'action_label' => 'View Meeting Details',
                'snoozed_until' => now()->addDays(2),
            ],

            // ===== RESOLVED NOTIFICATION =====
            [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'risk_resolved',
                'title' => 'Risk Level Normalized',
                'body' => 'Maria Gonzalez\'s risk level has returned to Low after improved attendance (3 consecutive weeks at 100%) and academic recovery.',
                'icon' => 'check-circle',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'status' => UserNotification::STATUS_RESOLVED,
                'action_url' => '/students',
                'action_label' => 'View Student',
            ],
        ];

        $created = 0;
        foreach ($notifications as $index => $data) {
            // Distribute across users - first user gets most, others get some
            $user = $index < 15 ? $primaryUser : $users->random();

            $notification = UserNotification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'category' => $data['category'],
                ],
                [
                    'org_id' => $school->id,
                    'type' => $data['type'],
                    'body' => $data['body'],
                    'icon' => $data['icon'],
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'action_url' => $data['action_url'],
                    'action_label' => $data['action_label'],
                    'metadata' => [],
                    'snoozed_until' => $data['snoozed_until'] ?? null,
                    'read_at' => $data['status'] === UserNotification::STATUS_READ ? now()->subHours(rand(1, 48)) : null,
                    'resolved_at' => $data['status'] === UserNotification::STATUS_RESOLVED ? now()->subHours(rand(1, 24)) : null,
                    'dismissed_at' => $data['status'] === UserNotification::STATUS_DISMISSED ? now()->subHours(rand(1, 72)) : null,
                    'created_by' => $admin->id,
                    'created_at' => now()->subHours(rand(1, 168)),
                ]
            );

            if ($notification->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("Created {$created} demo alerts/notifications.");
        $this->command->info('Visit /alerts to view the notification center.');
    }
}
