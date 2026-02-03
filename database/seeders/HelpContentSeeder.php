<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HelpContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default categories
        $categories = [
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Learn the basics of using Pulse',
                'icon' => 'play-circle',
                'sort_order' => 0,
            ],
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'description' => 'Managing your dashboard and widgets',
                'icon' => 'home',
                'sort_order' => 1,
            ],
            [
                'name' => 'Data Collection',
                'slug' => 'data-collection',
                'description' => 'Setting up surveys and data collection',
                'icon' => 'clipboard-document-list',
                'sort_order' => 2,
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'description' => 'Creating and sharing reports',
                'icon' => 'chart-bar',
                'sort_order' => 3,
            ],
            [
                'name' => 'Distribution',
                'slug' => 'distribution',
                'description' => 'Sending communications to recipients',
                'icon' => 'paper-airplane',
                'sort_order' => 4,
            ],
            [
                'name' => 'Administration',
                'slug' => 'administration',
                'description' => 'Admin settings and organization management',
                'icon' => 'cog-6-tooth',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            HelpCategory::updateOrCreate(
                ['org_id' => null, 'slug' => $categoryData['slug']],
                array_merge($categoryData, ['org_id' => null, 'is_active' => true])
            );
        }

        // Create sample articles
        $gettingStartedCategory = HelpCategory::where('slug', 'getting-started')->first();
        $dashboardCategory = HelpCategory::where('slug', 'dashboard')->first();

        $articles = [
            [
                'category_id' => $gettingStartedCategory?->id,
                'title' => 'Welcome to Pulse',
                'slug' => 'welcome-to-pulse',
                'content' => "# Welcome to Pulse\n\nPulse is your comprehensive platform for participant wellness monitoring and support. This guide will help you get started quickly.\n\n## What you can do with Pulse\n\n- **Monitor participant wellness** through surveys and assessments\n- **Track trends** with customizable dashboards\n- **Generate reports** for stakeholders\n- **Distribute communications** to participants and staff\n- **Manage resources** and support materials\n\n## Next Steps\n\n1. Set up your dashboard with the widgets you need\n2. Create your first survey\n3. Explore the resource hub\n\nNeed help? Click the help button in the bottom right corner anytime.",
                'excerpt' => 'Learn the basics of Pulse and what you can accomplish with the platform.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['welcome', 'intro', 'start', 'overview'],
            ],
            [
                'category_id' => $gettingStartedCategory?->id,
                'title' => 'Navigating the Interface',
                'slug' => 'navigating-the-interface',
                'content' => "# Navigating the Interface\n\nPulse has a clean, intuitive interface designed for efficiency.\n\n## Main Navigation\n\nThe sidebar provides access to all main features:\n\n- **Dashboard** - Your customizable home base\n- **Collect** - Create and manage data collections\n- **Distribute** - Send communications\n- **Reports** - Build and share reports\n- **Resources** - Access support materials\n- **Plans** - Strategic planning tools\n\n## Quick Actions\n\nLook for the quick action buttons in the top right of each page for common tasks.\n\n## Help Features\n\n- **Page Tours** - Click \"Start Tour\" to get a guided walkthrough of any page\n- **Help Hints** - Enable contextual hints to see tips throughout the interface\n- **Help Center** - Access articles and guides anytime",
                'excerpt' => 'Learn how to navigate around Pulse and find what you need.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['navigation', 'sidebar', 'menu', 'interface'],
            ],
            [
                'category_id' => $dashboardCategory?->id,
                'title' => 'Customizing Your Dashboard',
                'slug' => 'customizing-your-dashboard',
                'content' => "# Customizing Your Dashboard\n\nYour dashboard is fully customizable to show the information most important to you.\n\n## Adding Widgets\n\n1. Click the **Add Widget** button in the top right\n2. Browse available widget types\n3. Select a widget to add it to your dashboard\n4. Drag widgets to rearrange them\n\n## Widget Types\n\n- **Metrics** - Key numbers at a glance\n- **Charts** - Visualize trends over time\n- **Lists** - Recent activities and alerts\n- **Tables** - Detailed data views\n\n## Multiple Dashboards\n\nCreate different dashboards for different purposes:\n\n- Daily monitoring dashboard\n- Weekly review dashboard\n- Executive summary dashboard\n\nSwitch between dashboards using the selector in the top left.\n\n## Date Range Filtering\n\nUse the date range picker to filter all dashboard data by time period.",
                'excerpt' => 'Learn how to customize your dashboard with widgets and create multiple views.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['dashboard', 'widgets', 'customize', 'add widget'],
            ],
        ];

        foreach ($articles as $articleData) {
            HelpArticle::updateOrCreate(
                ['org_id' => null, 'slug' => $articleData['slug']],
                array_merge($articleData, [
                    'org_id' => null,
                    'published_at' => now(),
                    'search_keywords' => $articleData['search_keywords'] ?? null,
                ])
            );
        }
    }
}
