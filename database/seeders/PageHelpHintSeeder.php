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
        $hints = [
            // Dashboard
            [
                'page_context' => 'dashboard',
                'section' => 'selector',
                'selector' => '[data-help="dashboard-selector"]',
                'title' => 'Dashboard Selector',
                'description' => 'Switch between different dashboards or create new ones. Each dashboard can have its own set of customized widgets.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'dashboard',
                'section' => 'actions',
                'selector' => '[data-help="dashboard-actions"]',
                'title' => 'Dashboard Actions',
                'description' => 'Add widgets, set date ranges, and manage your dashboard settings. Customize your view to focus on what matters most.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
            [
                'page_context' => 'dashboard',
                'section' => 'date-range',
                'selector' => '[data-help="date-range"]',
                'title' => 'Date Range Filter',
                'description' => 'Filter your dashboard data by week, month, or quarter to see trends over different time periods.',
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

            // Reports
            [
                'page_context' => 'reports',
                'section' => 'search',
                'selector' => '[data-help="search-reports"]',
                'title' => 'Search Reports',
                'description' => 'Quickly find specific reports by searching for keywords in the report name.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'reports',
                'section' => 'filters',
                'selector' => '[data-help="report-filters"]',
                'title' => 'Filter Reports',
                'description' => 'Filter reports by status (Draft or Published) and switch between grid, list, and table views.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
            [
                'page_context' => 'reports',
                'section' => 'list',
                'selector' => '[data-help="report-list"]',
                'title' => 'Your Reports',
                'description' => 'View all your reports here. Click to edit, duplicate, or delete reports. Published reports can be shared with stakeholders.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // Collect
            [
                'page_context' => 'collect',
                'section' => 'search',
                'selector' => '[data-help="search-collections"]',
                'title' => 'Search Collections',
                'description' => 'Quickly find specific data collections by searching for keywords in the collection name.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'collect',
                'section' => 'filters',
                'selector' => '[data-help="collection-filters"]',
                'title' => 'Filter Collections',
                'description' => 'Filter by status (Active, Paused, Draft) or type (Recurring, One-time, Event-triggered) to narrow your view.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
            [
                'page_context' => 'collect',
                'section' => 'list',
                'selector' => '[data-help="collection-list"]',
                'title' => 'Your Collections',
                'description' => 'View all data collections here. Each card shows session counts, entries, and next scheduled run time.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // Distribute
            [
                'page_context' => 'distribute',
                'section' => 'search',
                'selector' => '[data-help="search-distributions"]',
                'title' => 'Search Distributions',
                'description' => 'Quickly find specific distributions by searching for keywords in the distribution name.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'distribute',
                'section' => 'filters',
                'selector' => '[data-help="distribution-filters"]',
                'title' => 'Filter Distributions',
                'description' => 'Filter by status or channel (Email, SMS) to find specific distributions quickly.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
            [
                'page_context' => 'distribute',
                'section' => 'list',
                'selector' => '[data-help="distribution-list"]',
                'title' => 'Your Distributions',
                'description' => 'Track all distributions here. See delivery counts, recipient lists, and next scheduled send time.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // Resources
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
                'section' => 'filters',
                'selector' => '[data-help="resource-filters"]',
                'title' => 'Filter & Sort',
                'description' => 'Use the sidebar to filter by category, content type, and sort order to narrow down your resource search.',
                'position' => 'right',
                'sort_order' => 1,
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

            // Contacts
            [
                'page_context' => 'contacts',
                'section' => 'search',
                'selector' => '[data-help="search-contacts"], input[type="search"], .search',
                'title' => 'Search Contacts',
                'description' => 'Search by name, email, or other criteria to quickly find specific contacts in your organization.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'contacts',
                'section' => 'list',
                'selector' => '[data-help="contact-list"], .contact-list, table',
                'title' => 'Contact Directory',
                'description' => 'Browse all contacts in your organization. Click on a contact to view their full profile.',
                'position' => 'top',
                'sort_order' => 1,
            ],

            // Plans
            [
                'page_context' => 'plans',
                'section' => 'search',
                'selector' => '[data-help="search-plans"], input[placeholder*="Search"]',
                'title' => 'Search Plans',
                'description' => 'Quickly find specific plans by searching for keywords in the plan name or description.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'plans',
                'section' => 'filters',
                'selector' => '[data-help="plan-filters"], select',
                'title' => 'Filter Plans',
                'description' => 'Filter plans by type (Growth, Strategic, Action, etc.) or status (Active, Draft, Completed) to narrow your view.',
                'position' => 'bottom',
                'sort_order' => 1,
            ],
            [
                'page_context' => 'plans',
                'section' => 'list',
                'selector' => '[data-help="plan-list"], .plan-list, [class*="plan-card"]',
                'title' => 'Plan Cards',
                'description' => 'View all strategic plans here. Each card shows the plan name, progress, goals, and key dates.',
                'position' => 'top',
                'sort_order' => 2,
            ],

            // Surveys
            [
                'page_context' => 'surveys',
                'section' => 'create',
                'selector' => '[data-help="create-survey"], [href*="create"]',
                'title' => 'Create Survey',
                'description' => 'Build a new wellness survey from templates or create custom questions tailored to your needs.',
                'position' => 'bottom',
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

            // Alerts
            [
                'page_context' => 'alerts',
                'section' => 'filters',
                'selector' => '[data-help="alert-filters"], .filters, [class*="filter"]',
                'title' => 'Filter Alerts',
                'description' => 'Use these filters to focus on specific alert types, severity levels, or time periods.',
                'position' => 'bottom',
                'sort_order' => 0,
            ],
            [
                'page_context' => 'alerts',
                'section' => 'list',
                'selector' => '[data-help="alert-list"], .alert-list, [class*="alert-item"]',
                'title' => 'Alert List',
                'description' => 'Each alert shows the learner, the trigger, and recommended actions. Click to view more details.',
                'position' => 'top',
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
                    'selector' => $hint['selector'],
                    'title' => $hint['title'],
                    'description' => $hint['description'],
                    'position' => $hint['position'],
                    'sort_order' => $hint['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
