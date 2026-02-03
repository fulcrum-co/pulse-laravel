<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\FocusArea;
use App\Models\Objective;
use App\Models\Organization;
use App\Models\StrategicPlan;
use App\Models\StrategyCollaborator;
use App\Models\Participant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StrategySeeder extends Seeder
{
    public function run(): void
    {
        // Get the first organization organization
        $organization = Organization::where('org_type', 'organization')->first();

        if (! $organization) {
            $this->command->warn('No organization organization found. Please run OrganizationSeeder first.');

            return;
        }

        // Get users
        $admin = User::where('org_id', $organization->id)->where('primary_role', 'admin')->first();
        $instructors = User::where('org_id', $organization->id)->where('primary_role', 'instructor')->get();
        $participants = Participant::where('org_id', $organization->id)->get();

        if (! $admin) {
            $this->command->warn('No admin user found. Please run UserSeeder first.');

            return;
        }

        // Create a 5-Year Strategic Plan
        $fiveYearPlan = StrategicPlan::create([
            'org_id' => $organization->id,
            'title' => 'Lincoln High Organization 5-Year Strategic Plan',
            'description' => 'Comprehensive strategic plan to improve participant wellness and academic outcomes over the next five years.',
            'plan_type' => 'organizational',
            'status' => 'active',
            'start_date' => Carbon::now()->startOfYear(),
            'end_date' => Carbon::now()->addYears(5)->endOfYear(),
            'created_by' => $admin->id,
        ]);

        // Add owner
        StrategyCollaborator::create([
            'strategic_plan_id' => $fiveYearPlan->id,
            'user_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Add instructors as collaborators
        foreach ($instructors->take(2) as $instructor) {
            StrategyCollaborator::create([
                'strategic_plan_id' => $fiveYearPlan->id,
                'user_id' => $instructor->id,
                'role' => 'collaborator',
            ]);
        }

        // Focus Area 1: Participant Mental Health & Wellness
        $fa1 = FocusArea::create([
            'strategic_plan_id' => $fiveYearPlan->id,
            'title' => 'Participant Mental Health & Wellness',
            'description' => 'Improve overall participant mental health and create a supportive organization environment.',
            'sort_order' => 0,
            'status' => 'on_track',
        ]);

        $obj1_1 = Objective::create([
            'focus_area_id' => $fa1->id,
            'title' => 'Implement universal mental health screening',
            'description' => 'Screen all participants for mental health concerns twice per year.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(6),
            'sort_order' => 0,
            'status' => 'on_track',
        ]);

        Activity::create([
            'objective_id' => $obj1_1->id,
            'title' => 'Select screening tool and vendor',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(2),
            'sort_order' => 0,
            'status' => 'on_track',
        ]);

        Activity::create([
            'objective_id' => $obj1_1->id,
            'title' => 'Train staff on screening protocols',
            'start_date' => Carbon::now()->addMonths(2),
            'end_date' => Carbon::now()->addMonths(4),
            'sort_order' => 1,
            'status' => 'not_started',
        ]);

        Activity::create([
            'objective_id' => $obj1_1->id,
            'title' => 'Conduct first round of screenings',
            'start_date' => Carbon::now()->addMonths(4),
            'end_date' => Carbon::now()->addMonths(6),
            'sort_order' => 2,
            'status' => 'not_started',
        ]);

        $obj1_2 = Objective::create([
            'focus_area_id' => $fa1->id,
            'title' => 'Expand counseling services',
            'description' => 'Increase counseling staff and availability.',
            'start_date' => Carbon::now()->addMonths(3),
            'end_date' => Carbon::now()->addYear(),
            'sort_order' => 1,
            'status' => 'at_risk',
        ]);

        Activity::create([
            'objective_id' => $obj1_2->id,
            'title' => 'Hire two additional support_persons',
            'start_date' => Carbon::now()->addMonths(3),
            'end_date' => Carbon::now()->addMonths(6),
            'sort_order' => 0,
            'status' => 'at_risk',
        ]);

        Activity::create([
            'objective_id' => $obj1_2->id,
            'title' => 'Establish peer counseling program',
            'start_date' => Carbon::now()->addMonths(6),
            'end_date' => Carbon::now()->addMonths(9),
            'sort_order' => 1,
            'status' => 'not_started',
        ]);

        // Focus Area 2: Academic Achievement
        $fa2 = FocusArea::create([
            'strategic_plan_id' => $fiveYearPlan->id,
            'title' => 'Academic Achievement',
            'description' => 'Improve academic outcomes for all participants.',
            'sort_order' => 1,
            'status' => 'at_risk',
        ]);

        $obj2_1 = Objective::create([
            'focus_area_id' => $fa2->id,
            'title' => 'Reduce achievement gaps',
            'description' => 'Close achievement gaps between participant populations.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addYears(2),
            'sort_order' => 0,
            'status' => 'at_risk',
        ]);

        Activity::create([
            'objective_id' => $obj2_1->id,
            'title' => 'Implement targeted tutoring program',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'sort_order' => 0,
            'status' => 'off_track',
        ]);

        Activity::create([
            'objective_id' => $obj2_1->id,
            'title' => 'Expand after-organization academic support',
            'start_date' => Carbon::now()->addMonths(3),
            'end_date' => Carbon::now()->addMonths(8),
            'sort_order' => 1,
            'status' => 'not_started',
        ]);

        // Focus Area 3: Family & Community Engagement
        $fa3 = FocusArea::create([
            'strategic_plan_id' => $fiveYearPlan->id,
            'title' => 'Family & Community Engagement',
            'description' => 'Strengthen partnerships with families and community organizations.',
            'sort_order' => 2,
            'status' => 'not_started',
        ]);

        $obj3_1 = Objective::create([
            'focus_area_id' => $fa3->id,
            'title' => 'Increase direct_supervisor involvement',
            'start_date' => Carbon::now()->addMonths(6),
            'end_date' => Carbon::now()->addYear(),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        Activity::create([
            'objective_id' => $obj3_1->id,
            'title' => 'Launch direct_supervisor communication app',
            'start_date' => Carbon::now()->addMonths(6),
            'end_date' => Carbon::now()->addMonths(8),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        Activity::create([
            'objective_id' => $obj3_1->id,
            'title' => 'Establish monthly direct_supervisor workshops',
            'start_date' => Carbon::now()->addMonths(8),
            'end_date' => Carbon::now()->addYear(),
            'sort_order' => 1,
            'status' => 'not_started',
        ]);

        // Create a shorter Annual Plan
        $annualPlan = StrategicPlan::create([
            'org_id' => $organization->id,
            'title' => '2025-2026 Organization Year Plan',
            'description' => 'Annual implementation plan for key wellness initiatives.',
            'plan_type' => 'organizational',
            'status' => 'draft',
            'start_date' => Carbon::create(2025, 8, 1),
            'end_date' => Carbon::create(2026, 6, 30),
            'created_by' => $admin->id,
        ]);

        StrategyCollaborator::create([
            'strategic_plan_id' => $annualPlan->id,
            'user_id' => $admin->id,
            'role' => 'owner',
        ]);

        $fa_annual = FocusArea::create([
            'strategic_plan_id' => $annualPlan->id,
            'title' => 'Wellness Check-ins',
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        Objective::create([
            'focus_area_id' => $fa_annual->id,
            'title' => 'Monthly wellness surveys for all participants',
            'start_date' => Carbon::create(2025, 9, 1),
            'end_date' => Carbon::create(2026, 5, 31),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        // Create Instructor Improvement Plans
        if ($instructors->count() >= 2) {
            $instructorPlan = StrategicPlan::create([
                'org_id' => $organization->id,
                'title' => 'Professional Growth Plan - '.$instructors[0]->first_name.' '.$instructors[0]->last_name,
                'description' => 'Individual improvement plan for learning_group management and participant engagement.',
                'plan_type' => 'instructor',
                'target_type' => 'App\\Models\\User',
                'target_id' => $instructors[0]->id,
                'status' => 'active',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYear(),
                'created_by' => $admin->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $instructorPlan->id,
                'user_id' => $admin->id,
                'role' => 'owner',
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $instructorPlan->id,
                'user_id' => $instructors[0]->id,
                'role' => 'collaborator',
            ]);

            $fa_instructor = FocusArea::create([
                'strategic_plan_id' => $instructorPlan->id,
                'title' => 'LearningGroup Management',
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            $obj_instructor = Objective::create([
                'focus_area_id' => $fa_instructor->id,
                'title' => 'Implement positive behavior interventions',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_instructor->id,
                'title' => 'Complete behavior support training',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_instructor->id,
                'title' => 'Implement reward system in learning_group',
                'start_date' => Carbon::now()->addMonths(2),
                'end_date' => Carbon::now()->addMonths(4),
                'sort_order' => 1,
                'status' => 'not_started',
            ]);
        }

        // Create Participant Improvement Plans
        if ($participants->count() >= 3) {
            // High-risk participant plan
            $highRiskLearner = $participants->where('risk_level', 'high')->first() ?? $participants->first();

            $learnerPlan = StrategicPlan::create([
                'org_id' => $organization->id,
                'title' => 'Participant Support Plan - '.$highRiskLearner->user->first_name.' '.$highRiskLearner->user->last_name,
                'description' => 'Individualized support plan addressing social-emotional needs.',
                'plan_type' => 'participant',
                'target_type' => 'App\\Models\\Participant',
                'target_id' => $highRiskLearner->id,
                'status' => 'active',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(4),
                'created_by' => $admin->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $learnerPlan->id,
                'user_id' => $admin->id,
                'role' => 'owner',
            ]);

            if ($instructors->count() > 0) {
                StrategyCollaborator::create([
                    'strategic_plan_id' => $learnerPlan->id,
                    'user_id' => $instructors[0]->id,
                    'role' => 'collaborator',
                ]);
            }

            $fa_learner = FocusArea::create([
                'strategic_plan_id' => $learnerPlan->id,
                'title' => 'Emotional Regulation',
                'sort_order' => 0,
                'status' => 'at_risk',
            ]);

            $obj_learner = Objective::create([
                'focus_area_id' => $fa_learner->id,
                'title' => 'Develop coping strategies',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'at_risk',
            ]);

            Activity::create([
                'objective_id' => $obj_learner->id,
                'title' => 'Weekly counseling sessions',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_learner->id,
                'title' => 'Mindfulness practice training',
                'start_date' => Carbon::now()->addWeeks(2),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 1,
                'status' => 'at_risk',
            ]);

            $fa_learner2 = FocusArea::create([
                'strategic_plan_id' => $learnerPlan->id,
                'title' => 'Academic Support',
                'sort_order' => 1,
                'status' => 'not_started',
            ]);

            $obj_learner2 = Objective::create([
                'focus_area_id' => $fa_learner2->id,
                'title' => 'Improve homework completion',
                'start_date' => Carbon::now()->addMonths(1),
                'end_date' => Carbon::now()->addMonths(3),
                'sort_order' => 0,
                'status' => 'not_started',
            ]);

            Activity::create([
                'objective_id' => $obj_learner2->id,
                'title' => 'Set up after-organization study sessions',
                'start_date' => Carbon::now()->addMonths(1),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'not_started',
            ]);
        }

        $this->command->info('Strategic plans seeded successfully!');
        $this->command->info('Created: 1 5-year plan, 1 annual plan, 1 instructor plan, 1 participant plan');
    }
}
