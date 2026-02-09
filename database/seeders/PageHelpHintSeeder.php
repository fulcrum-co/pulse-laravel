<?php

namespace Database\Seeders;

use App\Models\PageHelpHint;
use Illuminate\Database\Seeder;

class PageHelpHintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, deactivate all existing hints (soft delete approach)
        PageHelpHint::whereNull('org_id')->update(['is_active' => false]);

        $hints = [
            // ============================================
            // CONTACTS
            // ============================================
            [
                'page_context' => 'contacts',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Student Profiles & Risk Monitoring',
                'description' => 'View comprehensive student profiles with risk scores, attendance patterns, and intervention history. Use filters to identify students who need immediate support.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // SURVEYS
            // ============================================
            [
                'page_context' => 'surveys',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Surveys & Assessments',
                'description' => 'Create & manage your reporting surveys.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'surveys',
                'section' => 'list',
                'selector' => '[data-help="survey-list"], .survey-list, table',
                'title' => 'Survey List',
                'description' => 'View all your surveys here. You can see their status, response rates, and take quick actions.',
                'position' => 'top',
                'sort_order' => 1,
            ],

            // ============================================
            // REPORTS - Keep card 1 only
            // ============================================
            [
                'page_context' => 'reports',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Data-Driven Insights',
                'description' => 'Generate custom reports on student outcomes, intervention effectiveness, and program impact. Export data for presentations and stakeholder meetings.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            // NEW: Hover tooltip on create button
            [
                'page_context' => 'reports',
                'section' => 'create-report-tooltip',
                'selector' => 'a[href*="reports/create"], [href*="reports.create"]',
                'title' => 'Your Reports Tell the Story',
                'description' => 'Your reports should help you visually capture the whole story of what is happening. With beautiful PDFs, website widgets to give the public real time view into your key impact metrics, and much more.',
                'position' => 'bottom',
                'sort_order' => 1,
                'trigger_event' => 'hover',
            ],

            // ============================================
            // DATA COLLECTIONS
            // ============================================
            [
                'page_context' => 'collect',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Collections & Organization',
                'description' => 'Organize students, resources, and programs into custom collections for easy access and targeted interventions.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            // NEW: Popup on create collection
            [
                'page_context' => 'collect',
                'section' => 'create-collection-popup',
                'selector' => 'a[href*="collect/create"], [href*="collection/create"]',
                'title' => 'Set Up Data Collection Campaigns',
                'description' => 'Set up Data collection Campaigns in a few simple steps.',
                'position' => 'bottom',
                'sort_order' => 1,
                'trigger_event' => 'click',
            ],

            // ============================================
            // DISTRIBUTIONS - Keep card 1 only
            // ============================================
            [
                'page_context' => 'distribute',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Distributions',
                'description' => 'Send reports and messages to targeted groups via email or SMS. Set up one-time or recurring campaigns.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // RESOURCE LIBRARY
            // ============================================
            [
                'page_context' => 'resources',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Support Resources Library',
                'description' => 'Browse curated mental health resources, academic support materials, and community services. Share resources directly with students and families.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'resources',
                'section' => 'categories',
                'selector' => '[data-help="resource-categories"]',
                'title' => 'Resource Categories',
                'description' => 'Browse resources by category - Content, Providers, Programs, and Courses. Click any card to explore that category.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // ============================================
            // MODERATION (NEW CONTEXT)
            // ============================================
            [
                'page_context' => 'moderation',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Resource Moderation',
                'description' => 'Your team\'s command center for reviewing flagged content, tracking events, and taking action across the platform.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // MARKETPLACE (NEW CONTEXT)
            // ============================================
            [
                'page_context' => 'marketplace',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Marketplace',
                'description' => 'Discover ready-to-use resources: evidence-based surveys, intervention plans, curriculum, and trusted care providers.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // PROVIDERS
            // ============================================
            [
                'page_context' => 'providers',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Provider Directory',
                'description' => 'Connect students with verified mental health professionals, tutors, and support specialists. Filter by specialty, availability, and insurance acceptance.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // PROGRAMS
            // ============================================
            [
                'page_context' => 'programs',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Intervention Programs',
                'description' => 'Discover evidence-based programs for mental health, academic support, and enrichment. Track student enrollment and measure program outcomes.',
                'position' => 'center',
                'sort_order' => 0,
            ],

            // ============================================
            // ALERT MANAGEMENT
            // ============================================
            [
                'page_context' => 'alerts',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Alert Notifications',
                'description' => 'See all of your notifications in one place.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            // NEW: After Start Tasks button
            [
                'page_context' => 'alerts',
                'section' => 'clearing-tasks',
                'selector' => 'button[onclick*="startTaskFlow"], [data-action="start-tasks"]',
                'title' => 'Clearing Your Tasks',
                'description' => 'Streamline your workflow one task at a time. As you complete each notification, click done, our workflow will take you forward in the process so you don\'t have to think about it.',
                'position' => 'bottom',
                'sort_order' => 1,
                'trigger_event' => 'after-click',
            ],

            // ============================================
            // ALERT NOTIFICATIONS SECTION (workflow builder page)
            // ============================================
            [
                'page_context' => 'alerts',
                'section' => 'workflow-builder',
                'selector' => '[data-help="alert-builder"], .alert-workflow-builder',
                'title' => 'Build Custom Alerts',
                'description' => 'Build custom alerts that notify specific people where their attention is needed. Give them the full context of why they are being notified, and let Pulse help you advocate for the support you need.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // ============================================
            // PLANS (keep existing from page-help-overlay.blade.php)
            // ============================================
            [
                'page_context' => 'plans',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Student Success Plans',
                'description' => 'This is where you can set plans at any level of your organization, from organization-wide to individual improvement plans. You can track all of the progress across your organization in one place.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'plans',
                'section' => 'list',
                'selector' => '[data-help="plan-list"], .plan-list, [class*="plan-card"]',
                'title' => 'Your Plans',
                'description' => 'See the status of every initiative at a glance — identify what\'s on track, what needs attention, and where to focus your energy next.',
                'position' => 'top',
                'sort_order' => 1,
            ],

            // ============================================
            // DASHBOARD (keep existing updated version)
            // ============================================
            [
                'page_context' => 'dashboard',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Welcome to Pulse Connect',
                'description' => 'Your central hub for student wellness insights. Monitor at-risk students, track intervention outcomes, and access real-time data to support student success.',
                'position' => 'center',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'dashboard',
                'section' => 'selector',
                'selector' => '[data-help="dashboard-selector"]',
                'title' => 'Dashboard Selector',
                'description' => 'Switch between dashboards or create your own. Each one can be tailored to a specific focus — like attendance trends, student wellbeing, or team goals.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
        ];

        foreach ($hints as $hint) {
            PageHelpHint::updateOrCreate(
                [
                    'org_id' => null,
                    'page_context' => $hint['page_context'],
                    'section' => $hint['section'],
                ],
                [
                    'selector' => $hint['selector'] ?? null,
                    'title' => $hint['title'],
                    'description' => $hint['description'],
                    'position' => $hint['position'] ?? 'bottom',
                    'sort_order' => $hint['sort_order'],
                    'is_active' => true,
                    'trigger_event' => $hint['trigger_event'] ?? null,
                    'video_url' => $hint['video_url'] ?? null,
                    'offset_x' => $hint['offset_x'] ?? 0,
                    'offset_y' => $hint['offset_y'] ?? 0,
                ]
            );
        }

        $this->command->info('Page help hints seeded successfully!');
    }
}
