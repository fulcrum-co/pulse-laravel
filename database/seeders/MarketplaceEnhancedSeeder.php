<?php

namespace Database\Seeders;

use App\Models\MarketplaceItem;
use App\Models\MarketplaceReview;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }
        
        $users = User::where('org_id', $school->id)->whereIn('primary_role', ['admin', 'teacher', 'counselor'])->get();
        if ($users->isEmpty()) { $users = collect([User::where('org_id', $school->id)->first()]); }

        $items = $this->createItems($school->id, $users->first()->id);
        $totalReviews = 0;

        foreach ($items as $item) {
            $numReviews = rand(0, 3);
            $reviewers = $users->random(min($numReviews, $users->count()));

            foreach ($reviewers as $reviewer) {
                MarketplaceReview::create([
                    'marketplace_item_id' => $item->id,
                    'user_id' => $reviewer->id,
                    'org_id' => $school->id,
                    'rating' => rand(3, 5),
                    'review_text' => collect(['Excellent resource!', 'Very helpful for our students.', 'Great quality content.', 'Highly recommend this.'])->random(),
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
                $totalReviews++;
            }
        }

        $this->command->info("Created {$items->count()} marketplace items with {$totalReviews} reviews");
    }

    private function createItems(int $orgId, int $userId): \Illuminate\Support\Collection
    {
        $itemDefs = [
            ['title' => 'Student Wellness Dashboard', 'desc' => 'Pre-built dashboard template for tracking student wellbeing', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Academic Progress Report', 'desc' => 'Comprehensive academic tracking report', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Attendance Analytics Dashboard', 'desc' => 'Visualize attendance patterns and trends', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'SEL Assessment Bundle', 'desc' => 'Complete social-emotional learning assessment suite', 'type' => 'survey_template', 'category' => 'assessments', 'price' => 0],
            ['title' => 'Career Readiness Plan', 'desc' => 'Structured plan for career exploration and readiness', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Behavior Intervention Protocol', 'desc' => 'Evidence-based behavior support plan template', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Mental Health Screening', 'desc' => 'Validated mental health screening tool', 'type' => 'survey_template', 'category' => 'assessments', 'price' => 0],
            ['title' => 'College Counseling Directory', 'desc' => 'Network of college counseling professionals', 'type' => 'provider_directory', 'category' => 'providers', 'price' => 0],
            ['title' => 'Tutoring Services Network', 'desc' => 'Connect with local tutoring providers', 'type' => 'provider_directory', 'category' => 'providers', 'price' => 0],
            ['title' => 'Crisis Response Resources', 'desc' => 'Curated crisis intervention resources', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'Parent Engagement Toolkit', 'desc' => 'Resources for improving parent communication', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'Data Collection Templates', 'desc' => 'Ready-to-use data collection forms', 'type' => 'collection_template', 'category' => 'data', 'price' => 0],
            ['title' => 'Student Success Metrics', 'desc' => 'Key performance indicators dashboard', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Transition Support Plan', 'desc' => 'Support students through transitions', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Trauma-Informed Practices', 'desc' => 'Training materials for trauma-informed approach', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'Restorative Justice Resources', 'desc' => 'Restorative practices implementation guide', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'MTSS Framework Dashboard', 'desc' => 'Multi-tiered support system tracking', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Student Voice Survey', 'desc' => 'Collect student feedback and perspectives', 'type' => 'survey_template', 'category' => 'assessments', 'price' => 0],
            ['title' => 'Academic Goal Tracker', 'desc' => 'Monitor student academic goals', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Behavioral Health Providers', 'desc' => 'Vetted mental health professionals', 'type' => 'provider_directory', 'category' => 'providers', 'price' => 0],
            ['title' => 'Graduation Tracking Dashboard', 'desc' => 'Monitor graduation requirements', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Credit Recovery Resources', 'desc' => 'Support for credit recovery programs', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'IEP Progress Monitoring', 'desc' => 'Track IEP goal progress', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'School Climate Survey', 'desc' => 'Assess school culture and climate', 'type' => 'survey_template', 'category' => 'assessments', 'price' => 0],
            ['title' => 'Equity Audit Dashboard', 'desc' => 'Analyze equity metrics across demographics', 'type' => 'report_template', 'category' => 'reports', 'price' => 0],
            ['title' => 'Family Engagement Plan', 'desc' => 'Structured family involvement strategy', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Student Strengths Inventory', 'desc' => 'Asset-based student assessment', 'type' => 'survey_template', 'category' => 'assessments', 'price' => 0],
            ['title' => 'Community Resource Directory', 'desc' => 'Local community support services', 'type' => 'provider_directory', 'category' => 'providers', 'price' => 0],
            ['title' => 'Attendance Improvement Plan', 'desc' => 'Intervention for chronic absenteeism', 'type' => 'plan_template', 'category' => 'plans', 'price' => 0],
            ['title' => 'Peer Mentorship Program', 'desc' => 'Implement peer support programs', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'Post-Secondary Planning', 'desc' => 'College and career readiness resources', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
            ['title' => 'Student Leadership Development', 'desc' => 'Build student leadership skills', 'type' => 'resource_bundle', 'category' => 'resources', 'price' => 0],
        ];

        return collect($itemDefs)->map(fn($d) => MarketplaceItem::create([
            'org_id' => $orgId, 'title' => $d['title'], 'description' => $d['desc'],
            'item_type' => $d['type'], 'category' => $d['category'],
            'status' => 'published', 'visibility' => 'public',
            'price' => $d['price'], 'created_by' => $userId,
        ]));
    }
}
