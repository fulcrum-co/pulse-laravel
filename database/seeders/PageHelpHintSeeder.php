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
            // CONTACTS - Keep only card 1
            // ============================================
            [
                'page_context' => 'contacts',
                'section' => 'search',
                'selector' => '[data-help="search-contacts"], input[type="search"], .search',
                'title' => 'Search Contacts',
                'description' => 'Search by name, email, or other criteria to quickly find specific contacts in your organization.',
                'position' => 'bottom',
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
                'description' => 'Create and manage all assessments in one central location.',
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
            // NEW: On create survey page
            [
                'page_context' => 'surveys',
                'section' => 'create-beyond-forms',
                'selector' => '[href*="surveys/create"], a[href*="create"]',
                'title' => 'Move Beyond Static Forms',
                'description' => 'Pulse makes completing assessments not only easier, but more holistic by allowing the person reporting the ability to give more context to what is happening, extract the data and apply it directly to the record being reported on.',
                'position' => 'bottom',
                'sort_order' => 2,
                'trigger_event' => 'click',
            ],

            // ============================================
            // REPORTS - Keep card 1 only
            // ============================================
            [
                'page_context' => 'reports',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Reports',
                'description' => 'Build beautiful, data-driven reports with our drag-and-drop editor. Share insights with stakeholders.',
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
                'title' => 'Data Collections',
                'description' => 'Set up recurring campaigns to collect data at scale, making it easier to ensure information is thorough and up to date.',
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
            // RESOURCE LIBRARY - Keep cards 1 & 3
            // ============================================
            [
                'page_context' => 'resources',
                'section' => 'search',
                'selector' => '[data-help="search-resources"]',
                'title' => 'Search Resources',
                'description' => 'Search across all resource types including content, providers, programs, and courses to quickly find what you need.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'resources',
                'section' => 'categories',
                'selector' => '[data-help="resource-categories"]',
                'title' => 'Resource Categories',
                'description' => 'Browse resources by category - Content, Providers, Programs, and Courses. Click any card to explore that category.',
                'position' => 'top',
                'sort_order' => 1,
            ],

            // ============================================
            // MODERATION (NEW CONTEXT)
            // ============================================
            [
                'page_context' => 'moderation',
                'section' => 'intro',
                'selector' => null,
                'title' => 'Resource Moderation',
                'description' => 'Your administration can now have a single place to oversee all resource changes and additions in your organization.',
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
                'description' => 'Find standardized reports, custom plans for your students, helpful learning resources and a directory of support providers for everyone in your school.',
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
                'title' => 'Plans',
                'description' => 'This is where your goals take shape. Create plans at any level — from school-wide initiatives down to individual student improvement — and track progress all in one place.',
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
                'title' => 'Welcome to your dashboard',
                'description' => 'This is your home base. Everything you need to stay informed and take action starts here.',
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
            [
                'page_context' => 'dashboard',
                'section' => 'actions',
                'selector' => '[data-help="dashboard-actions"]',
                'title' => 'Dashboard Actions',
                'description' => 'Add widgets, set date ranges, and manage your dashboard settings. Customize your view to focus on what matters most.',
                'position' => 'bottom',
                'sort_order' => 2,
            ],
            [
                'page_context' => 'dashboard',
                'section' => 'widgets',
                'selector' => '[data-help="widgets-grid"]',
                'title' => 'Dashboard Widgets',
                'description' => 'Your customizable widgets display key metrics, charts, and lists. Add, remove, or rearrange widgets to build your perfect dashboard.',
                'position' => 'top',
                'sort_order' => 3,
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
