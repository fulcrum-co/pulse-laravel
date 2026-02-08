<?php

namespace Database\Seeders;

use App\Models\MarketplaceItem;
use App\Models\MarketplaceReview;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }

        $users = User::where('org_id', $school->id)->whereIn('primary_role', ['admin', 'teacher', 'counselor'])->get();
        if ($users->isEmpty()) { $users = collect([User::where('org_id', $school->id)->first()]); }

        // Create seller profile for the admin user
        $seller = SellerProfile::firstOrCreate(
            ['user_id' => $users->first()->id],
            [
                'org_id' => $school->id,
                'display_name' => $school->name . ' Resources',
                'slug' => Str::slug($school->name) . '-resources',
                'bio' => 'Official resource marketplace for ' . $school->name,
                'seller_type' => 'organization',
                'is_verified' => true,
                'verified_at' => now(),
                'active' => true,
            ]
        );

        // Get existing resources to link marketplace items to
        $resources = Resource::where('org_id', $school->id)->get();

        $items = $this->createItems($school->id, $users->first()->id, $seller->id, $resources);
        $totalReviews = 0;

        foreach ($items as $item) {
            $numReviews = rand(0, 3);
            $reviewers = $users->random(min($numReviews, $users->count()));

            foreach ($reviewers as $reviewer) {
                MarketplaceReview::firstOrCreate(
                    [
                        'marketplace_item_id' => $item->id,
                        'user_id' => $reviewer->id,
                    ],
                    [
                        'org_id' => $school->id,
                        'rating' => rand(3, 5),
                        'review_text' => collect(['Excellent resource!', 'Very helpful for our students.', 'Great quality content.', 'Highly recommend this.'])->random(),
                        'created_at' => now()->subDays(rand(1, 60)),
                    ]
                );
                $totalReviews++;
            }
        }

        $this->command->info("Created {$items->count()} marketplace items with {$totalReviews} reviews");
    }

    private function createItems(int $orgId, int $userId, int $sellerId, $resources): \Illuminate\Support\Collection
    {
        $itemDefs = [
            ['title' => 'Student Wellness Dashboard', 'desc' => 'Pre-built dashboard template for tracking student wellbeing', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Academic Progress Report', 'desc' => 'Comprehensive academic tracking report', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Attendance Analytics Dashboard', 'desc' => 'Visualize attendance patterns and trends', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'SEL Assessment Bundle', 'desc' => 'Complete social-emotional learning assessment suite', 'category' => MarketplaceItem::CATEGORY_SURVEY, 'pricing' => 'free'],
            ['title' => 'Career Readiness Plan', 'desc' => 'Structured plan for career exploration and readiness', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Behavior Intervention Protocol', 'desc' => 'Evidence-based behavior support plan template', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Mental Health Screening', 'desc' => 'Validated mental health screening tool', 'category' => MarketplaceItem::CATEGORY_SURVEY, 'pricing' => 'free'],
            ['title' => 'College Counseling Directory', 'desc' => 'Network of college counseling professionals', 'category' => MarketplaceItem::CATEGORY_PROVIDER, 'pricing' => 'free'],
            ['title' => 'Tutoring Services Network', 'desc' => 'Connect with local tutoring providers', 'category' => MarketplaceItem::CATEGORY_PROVIDER, 'pricing' => 'free'],
            ['title' => 'Crisis Response Resources', 'desc' => 'Curated crisis intervention resources', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Parent Engagement Toolkit', 'desc' => 'Resources for improving parent communication', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Data Collection Templates', 'desc' => 'Ready-to-use data collection forms', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Student Success Metrics', 'desc' => 'Key performance indicators dashboard', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Transition Support Plan', 'desc' => 'Support students through transitions', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Trauma-Informed Practices', 'desc' => 'Training materials for trauma-informed approach', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Restorative Justice Resources', 'desc' => 'Restorative practices implementation guide', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'MTSS Framework Dashboard', 'desc' => 'Multi-tiered support system tracking', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Student Voice Survey', 'desc' => 'Collect student feedback and perspectives', 'category' => MarketplaceItem::CATEGORY_SURVEY, 'pricing' => 'free'],
            ['title' => 'Academic Goal Tracker', 'desc' => 'Monitor student academic goals', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Behavioral Health Providers', 'desc' => 'Vetted mental health professionals', 'category' => MarketplaceItem::CATEGORY_PROVIDER, 'pricing' => 'free'],
            ['title' => 'Graduation Tracking Dashboard', 'desc' => 'Monitor graduation requirements', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Credit Recovery Resources', 'desc' => 'Support for credit recovery programs', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'IEP Progress Monitoring', 'desc' => 'Track IEP goal progress', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'School Climate Survey', 'desc' => 'Assess school culture and climate', 'category' => MarketplaceItem::CATEGORY_SURVEY, 'pricing' => 'free'],
            ['title' => 'Equity Audit Dashboard', 'desc' => 'Analyze equity metrics across demographics', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Family Engagement Plan', 'desc' => 'Structured family involvement strategy', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Student Strengths Inventory', 'desc' => 'Asset-based student assessment', 'category' => MarketplaceItem::CATEGORY_SURVEY, 'pricing' => 'free'],
            ['title' => 'Community Resource Directory', 'desc' => 'Local community support services', 'category' => MarketplaceItem::CATEGORY_PROVIDER, 'pricing' => 'free'],
            ['title' => 'Attendance Improvement Plan', 'desc' => 'Intervention for chronic absenteeism', 'category' => MarketplaceItem::CATEGORY_STRATEGY, 'pricing' => 'free'],
            ['title' => 'Peer Mentorship Program', 'desc' => 'Implement peer support programs', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Post-Secondary Planning', 'desc' => 'College and career readiness resources', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
            ['title' => 'Student Leadership Development', 'desc' => 'Build student leadership skills', 'category' => MarketplaceItem::CATEGORY_CONTENT, 'pricing' => 'free'],
        ];

        // Link marketplace items to existing resources (cycling through if needed)
        $resourceIndex = 0;
        return collect($itemDefs)->map(function($d) use ($orgId, $userId, $sellerId, $resources, &$resourceIndex) {
            // Get a resource to link to (cycle through available resources)
            $resource = $resources->isNotEmpty() ? $resources[$resourceIndex % $resources->count()] : null;
            $resourceIndex++;

            return MarketplaceItem::firstOrCreate(
                [
                    'org_id' => $orgId,
                    'title' => $d['title'],
                ],
                [
                    'seller_profile_id' => $sellerId,
                    'description' => $d['desc'],
                    'category' => $d['category'],
                    'listable_type' => $resource ? 'App\\Models\\Resource' : 'App\\Models\\Organization',
                    'listable_id' => $resource ? $resource->id : $orgId,
                    'pricing_type' => $d['pricing'],
                    'status' => MarketplaceItem::STATUS_APPROVED,
                    'published_at' => now()->subDays(rand(1, 90)),
                    'created_by' => $userId,
                ]
            );
        });
    }
}
