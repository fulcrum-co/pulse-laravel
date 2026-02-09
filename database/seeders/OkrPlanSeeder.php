<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\KeyResult;
use App\Models\Milestone;
use App\Models\Organization;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use App\Models\StrategyCollaborator;
use App\Models\StrategyDriftScore;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OkrPlanSeeder extends Seeder
{
    public function run(): void
    {
        // Get first available organization
        $org = Organization::first();

        if (! $org) {
            $this->command->warn('No organization found. Creating a demo organization...');
            $org = Organization::create([
                'name' => 'Demo Organization',
                'org_type' => 'school',
                'status' => 'active',
            ]);
        }

        // Get first available user or create one
        $user = User::where('org_id', $org->id)->first() ?? User::first();

        if (! $user) {
            $this->command->warn('No user found. Please create a user first.');

            return;
        }

        $this->command->info('Creating OKR plans for: '.$org->name);

        // Get other users for collaborators
        $otherUsers = User::where('org_id', $org->id)->where('id', '!=', $user->id)->take(3)->get();
        $students = Student::where('org_id', $org->id)->get();

        // =====================================================
        // 1. School-Wide Strategic OKR Plan
        // =====================================================
        $strategicPlan = StrategicPlan::create([
            'org_id' => $org->id,
            'title' => 'Q1 2025 Wellness Initiative',
            'description' => 'Quarterly OKR plan to improve mental health outcomes and reduce chronic absenteeism.',
            'plan_type' => StrategicPlan::TYPE_STRATEGIC,
            'category' => StrategicPlan::CATEGORY_OKR,
            'status' => StrategicPlan::STATUS_ACTIVE,
            'start_date' => Carbon::now()->startOfQuarter(),
            'end_date' => Carbon::now()->endOfQuarter(),
            'created_by' => $user->id,
        ]);

        StrategyCollaborator::create([
            'strategic_plan_id' => $strategicPlan->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        foreach ($otherUsers as $otherUser) {
            StrategyCollaborator::create([
                'strategic_plan_id' => $strategicPlan->id,
                'user_id' => $otherUser->id,
                'role' => 'collaborator',
            ]);
        }

        // Focus Area 1: Mental Health Support
        $goal1 = Goal::create([
            'strategic_plan_id' => $strategicPlan->id,
            'title' => 'Improve Mental Health Support',
            'description' => 'Expand and enhance mental health resources and accessibility for all.',
            'goal_type' => Goal::TYPE_OBJECTIVE,
            'status' => Goal::STATUS_IN_PROGRESS,
            'due_date' => Carbon::now()->endOfQuarter(),
            'sort_order' => 0,
            'owner_id' => $user->id,
        ]);

        // Key Activities for Goal 1
        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Increase counseling appointment capacity',
            'description' => 'Add more counseling slots to reduce wait times from 2 weeks to 3 days.',
            'metric_type' => KeyResult::METRIC_NUMBER,
            'target_value' => 50,
            'current_value' => 32,
            'starting_value' => 20,
            'unit' => 'appointments/week',
            'due_date' => Carbon::now()->endOfQuarter(),
            'status' => KeyResult::STATUS_ON_TRACK,
            'sort_order' => 0,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Train staff on mental health first aid',
            'description' => 'All team members complete mental health first aid certification.',
            'metric_type' => KeyResult::METRIC_PERCENTAGE,
            'target_value' => 100,
            'current_value' => 65,
            'starting_value' => 0,
            'unit' => null,
            'due_date' => Carbon::now()->addMonths(2),
            'status' => KeyResult::STATUS_IN_PROGRESS,
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Launch peer support program',
            'description' => 'Recruit and train wellness ambassadors.',
            'metric_type' => KeyResult::METRIC_NUMBER,
            'target_value' => 25,
            'current_value' => 8,
            'starting_value' => 0,
            'unit' => 'ambassadors',
            'due_date' => Carbon::now()->addMonths(2),
            'status' => KeyResult::STATUS_AT_RISK,
            'sort_order' => 2,
        ]);

        // Focus Area 2: Reduce Absenteeism
        $goal2 = Goal::create([
            'strategic_plan_id' => $strategicPlan->id,
            'title' => 'Reduce Chronic Absenteeism',
            'description' => 'Implement early intervention strategies to improve attendance rates.',
            'goal_type' => Goal::TYPE_OBJECTIVE,
            'status' => Goal::STATUS_AT_RISK,
            'due_date' => Carbon::now()->endOfQuarter(),
            'sort_order' => 1,
            'owner_id' => $otherUsers->first()?->id ?? $user->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Reduce chronic absenteeism rate',
            'description' => 'People missing 10%+ of scheduled time.',
            'metric_type' => KeyResult::METRIC_PERCENTAGE,
            'target_value' => 8,
            'current_value' => 12,
            'starting_value' => 15,
            'unit' => null,
            'due_date' => Carbon::now()->endOfQuarter(),
            'status' => KeyResult::STATUS_AT_RISK,
            'sort_order' => 0,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Implement attendance early warning system',
            'description' => 'Automated alerts for 3+ absences in 30 days.',
            'metric_type' => KeyResult::METRIC_BOOLEAN,
            'target_value' => 1,
            'current_value' => 1,
            'starting_value' => 0,
            'unit' => null,
            'due_date' => Carbon::now()->addMonth(),
            'status' => KeyResult::STATUS_COMPLETED,
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Engagement meetings held',
            'description' => 'Conduct intervention meetings with families of chronically absent individuals.',
            'metric_type' => KeyResult::METRIC_NUMBER,
            'target_value' => 40,
            'current_value' => 18,
            'starting_value' => 0,
            'unit' => 'meetings',
            'due_date' => Carbon::now()->endOfQuarter(),
            'status' => KeyResult::STATUS_IN_PROGRESS,
            'sort_order' => 2,
        ]);

        // Focus Area 3: Family Engagement
        $goal3 = Goal::create([
            'strategic_plan_id' => $strategicPlan->id,
            'title' => 'Strengthen Family Communication',
            'description' => 'Improve two-way communication channels with families.',
            'goal_type' => Goal::TYPE_OBJECTIVE,
            'status' => Goal::STATUS_NOT_STARTED,
            'due_date' => Carbon::now()->endOfQuarter(),
            'sort_order' => 2,
            'owner_id' => $user->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal3->id,
            'title' => 'Launch communication app',
            'description' => 'Deploy and onboard families to new communication platform.',
            'metric_type' => KeyResult::METRIC_PERCENTAGE,
            'target_value' => 80,
            'current_value' => 0,
            'starting_value' => 0,
            'unit' => 'families onboarded',
            'due_date' => Carbon::now()->addMonths(2),
            'status' => KeyResult::STATUS_NOT_STARTED,
            'sort_order' => 0,
        ]);

        KeyResult::create([
            'goal_id' => $goal3->id,
            'title' => 'Monthly newsletter subscribers',
            'description' => 'Grow engagement through regular communications.',
            'metric_type' => KeyResult::METRIC_NUMBER,
            'target_value' => 500,
            'current_value' => 0,
            'starting_value' => 0,
            'unit' => 'subscribers',
            'due_date' => Carbon::now()->endOfQuarter(),
            'status' => KeyResult::STATUS_NOT_STARTED,
            'sort_order' => 1,
        ]);

        // Milestones for Strategic Plan
        Milestone::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal1->id,
            'title' => 'Complete counselor hiring',
            'description' => 'Hire and onboard 2 additional counselors.',
            'due_date' => Carbon::now()->addWeeks(2),
            'status' => Milestone::STATUS_COMPLETED,
            'completed_at' => Carbon::now()->subDays(3),
            'completed_by' => $user->id,
            'sort_order' => 0,
        ]);

        Milestone::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal1->id,
            'title' => 'Mental health training kickoff',
            'description' => 'Begin first cohort of mental health first aid training.',
            'due_date' => Carbon::now()->addWeeks(4),
            'status' => Milestone::STATUS_IN_PROGRESS,
            'sort_order' => 1,
        ]);

        Milestone::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal2->id,
            'title' => 'Launch attendance dashboard',
            'description' => 'Deploy real-time attendance monitoring for administrators.',
            'due_date' => Carbon::now()->addDays(5),
            'status' => Milestone::STATUS_IN_PROGRESS,
            'sort_order' => 2,
        ]);

        Milestone::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => null,
            'title' => 'Quarterly progress review',
            'description' => 'Present Q1 progress to leadership.',
            'due_date' => Carbon::now()->endOfQuarter(),
            'status' => Milestone::STATUS_PENDING,
            'sort_order' => 3,
        ]);

        // Progress Updates
        ProgressUpdate::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal1->id,
            'content' => 'Successfully hired two new counselors. They start next week and will focus on high-priority cases.',
            'update_type' => ProgressUpdate::TYPE_MANUAL,
            'created_by' => $user->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal1->id,
            'content' => '15 staff members completed the mental health first aid certification this week. Training feedback has been very positive.',
            'update_type' => ProgressUpdate::TYPE_MANUAL,
            'value_change' => 15,
            'created_by' => $otherUsers->first()?->id ?? $user->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => $goal2->id,
            'content' => 'The attendance early warning system is now live. Already identified 23 individuals needing intervention.',
            'update_type' => ProgressUpdate::TYPE_SYSTEM,
            'created_by' => $user->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $strategicPlan->id,
            'goal_id' => null,
            'content' => 'Weekly analysis: Overall plan is 45% complete. Mental health focus area is ahead of schedule, but absenteeism metrics need attention. Recommend prioritizing engagement meetings.',
            'update_type' => ProgressUpdate::TYPE_AI_GENERATED,
            'created_by' => $user->id,
            'created_at' => Carbon::now()->subHours(6),
        ]);

        // =====================================================
        // 2. Individual Growth Plan (IDP)
        // =====================================================
        if ($otherUsers->count() >= 1) {
            $targetUser = $otherUsers->first();

            $growthPlan = StrategicPlan::create([
                'org_id' => $org->id,
                'title' => 'Professional Growth Plan - '.$targetUser->first_name.' '.$targetUser->last_name,
                'description' => 'Individual development plan focused on engagement and technology integration.',
                'plan_type' => StrategicPlan::TYPE_GROWTH,
                'category' => StrategicPlan::CATEGORY_IDP,
                'target_type' => 'App\\Models\\User',
                'target_id' => $targetUser->id,
                'status' => StrategicPlan::STATUS_ACTIVE,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(6),
                'created_by' => $user->id,
                'manager_id' => $user->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $growthPlan->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $growthPlan->id,
                'user_id' => $targetUser->id,
                'role' => 'collaborator',
            ]);

            $growthGoal1 = Goal::create([
                'strategic_plan_id' => $growthPlan->id,
                'title' => 'Improve Client Engagement',
                'description' => 'Implement active strategies to increase participation.',
                'goal_type' => Goal::TYPE_OBJECTIVE,
                'status' => Goal::STATUS_IN_PROGRESS,
                'due_date' => Carbon::now()->addMonths(3),
                'sort_order' => 0,
                'owner_id' => $targetUser->id,
            ]);

            KeyResult::create([
                'goal_id' => $growthGoal1->id,
                'title' => 'Increase participation rate',
                'description' => 'Measure through daily check-ins and discussions.',
                'metric_type' => KeyResult::METRIC_PERCENTAGE,
                'target_value' => 85,
                'current_value' => 62,
                'starting_value' => 55,
                'unit' => null,
                'due_date' => Carbon::now()->addMonths(3),
                'status' => KeyResult::STATUS_IN_PROGRESS,
                'sort_order' => 0,
            ]);

            KeyResult::create([
                'goal_id' => $growthGoal1->id,
                'title' => 'Complete workshop series',
                'description' => 'Attend all 4 professional development sessions.',
                'metric_type' => KeyResult::METRIC_NUMBER,
                'target_value' => 4,
                'current_value' => 2,
                'starting_value' => 0,
                'unit' => 'workshops',
                'due_date' => Carbon::now()->addMonths(2),
                'status' => KeyResult::STATUS_ON_TRACK,
                'sort_order' => 1,
            ]);

            $growthGoal2 = Goal::create([
                'strategic_plan_id' => $growthPlan->id,
                'title' => 'Technology Integration',
                'description' => 'Effectively integrate technology tools into daily work.',
                'goal_type' => Goal::TYPE_OBJECTIVE,
                'status' => Goal::STATUS_NOT_STARTED,
                'due_date' => Carbon::now()->addMonths(6),
                'sort_order' => 1,
                'owner_id' => $targetUser->id,
            ]);

            KeyResult::create([
                'goal_id' => $growthGoal2->id,
                'title' => 'Sessions using digital tools',
                'description' => 'Incorporate interactive technology in weekly sessions.',
                'metric_type' => KeyResult::METRIC_NUMBER,
                'target_value' => 3,
                'current_value' => 0,
                'starting_value' => 0,
                'unit' => 'sessions/week',
                'due_date' => Carbon::now()->addMonths(6),
                'status' => KeyResult::STATUS_NOT_STARTED,
                'sort_order' => 0,
            ]);

            Milestone::create([
                'strategic_plan_id' => $growthPlan->id,
                'goal_id' => $growthGoal1->id,
                'title' => 'Mid-period check-in with mentor',
                'due_date' => Carbon::now()->addMonths(2),
                'status' => Milestone::STATUS_PENDING,
                'sort_order' => 0,
            ]);
        }

        // =====================================================
        // 3. Action Plan (for a Student if available)
        // =====================================================
        if ($students->count() >= 1) {
            $student = $students->first();

            $actionPlan = StrategicPlan::create([
                'org_id' => $org->id,
                'title' => 'Support Plan - '.$student->user->first_name.' '.$student->user->last_name,
                'description' => 'Targeted intervention plan addressing attendance and emotional regulation.',
                'plan_type' => StrategicPlan::TYPE_ACTION,
                'category' => StrategicPlan::CATEGORY_ACTION_PLAN,
                'target_type' => 'App\\Models\\Student',
                'target_id' => $student->id,
                'status' => StrategicPlan::STATUS_ACTIVE,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'created_by' => $user->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $actionPlan->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            $actionGoal1 = Goal::create([
                'strategic_plan_id' => $actionPlan->id,
                'title' => 'Improve Attendance',
                'description' => 'Reduce unexcused absences through targeted support.',
                'goal_type' => Goal::TYPE_OBJECTIVE,
                'status' => Goal::STATUS_AT_RISK,
                'due_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'owner_id' => $user->id,
            ]);

            KeyResult::create([
                'goal_id' => $actionGoal1->id,
                'title' => 'Weekly attendance rate',
                'description' => 'Maintain consistent attendance.',
                'metric_type' => KeyResult::METRIC_PERCENTAGE,
                'target_value' => 95,
                'current_value' => 72,
                'starting_value' => 65,
                'unit' => null,
                'due_date' => Carbon::now()->addMonths(2),
                'status' => KeyResult::STATUS_AT_RISK,
                'sort_order' => 0,
            ]);

            KeyResult::create([
                'goal_id' => $actionGoal1->id,
                'title' => 'Morning check-ins completed',
                'description' => 'Daily check-in with counselor or trusted adult.',
                'metric_type' => KeyResult::METRIC_PERCENTAGE,
                'target_value' => 100,
                'current_value' => 80,
                'starting_value' => 0,
                'unit' => null,
                'due_date' => Carbon::now()->addMonth(),
                'status' => KeyResult::STATUS_ON_TRACK,
                'sort_order' => 1,
            ]);

            $actionGoal2 = Goal::create([
                'strategic_plan_id' => $actionPlan->id,
                'title' => 'Emotional Regulation Skills',
                'description' => 'Develop coping strategies for managing stress and anxiety.',
                'goal_type' => Goal::TYPE_OBJECTIVE,
                'status' => Goal::STATUS_IN_PROGRESS,
                'due_date' => Carbon::now()->addMonths(2),
                'sort_order' => 1,
                'owner_id' => $user->id,
            ]);

            KeyResult::create([
                'goal_id' => $actionGoal2->id,
                'title' => 'Counseling sessions attended',
                'description' => 'Weekly individual counseling sessions.',
                'metric_type' => KeyResult::METRIC_NUMBER,
                'target_value' => 8,
                'current_value' => 3,
                'starting_value' => 0,
                'unit' => 'sessions',
                'due_date' => Carbon::now()->addMonths(2),
                'status' => KeyResult::STATUS_ON_TRACK,
                'sort_order' => 0,
            ]);

            KeyResult::create([
                'goal_id' => $actionGoal2->id,
                'title' => 'Self-reported stress level',
                'description' => 'Weekly wellness check-in score (1-10 scale, lower is better).',
                'metric_type' => KeyResult::METRIC_NUMBER,
                'target_value' => 4,
                'current_value' => 6,
                'starting_value' => 8,
                'unit' => 'avg score',
                'due_date' => Carbon::now()->addMonths(2),
                'status' => KeyResult::STATUS_IN_PROGRESS,
                'sort_order' => 1,
            ]);

            Milestone::create([
                'strategic_plan_id' => $actionPlan->id,
                'goal_id' => $actionGoal1->id,
                'title' => 'Family conference',
                'description' => 'Meet with family to discuss progress and adjust supports.',
                'due_date' => Carbon::now()->addWeeks(3),
                'status' => Milestone::STATUS_PENDING,
                'sort_order' => 0,
            ]);

            Milestone::create([
                'strategic_plan_id' => $actionPlan->id,
                'goal_id' => $actionGoal2->id,
                'title' => 'Complete coping skills workbook',
                'due_date' => Carbon::now()->addMonths(1),
                'status' => Milestone::STATUS_IN_PROGRESS,
                'sort_order' => 1,
            ]);

            ProgressUpdate::create([
                'strategic_plan_id' => $actionPlan->id,
                'goal_id' => $actionGoal2->id,
                'content' => 'Has attended 3 of 8 scheduled counseling sessions. Showing improvement in identifying emotions and using breathing techniques.',
                'update_type' => ProgressUpdate::TYPE_MANUAL,
                'created_by' => $user->id,
                'created_at' => Carbon::now()->subDays(2),
            ]);
        }

        // =====================================================
        // 4. Create Strategy Drift Scores (for alignment demo)
        // =====================================================
        $this->createDriftScores($org, $strategicPlan);

        $this->command->info('OKR-style plans seeded successfully!');
        $this->command->info('Created: 1 strategic OKR plan with goals, key activities, milestones, and progress updates');
        if ($otherUsers->count() >= 1) {
            $this->command->info('Created: 1 individual growth plan');
        }
        if ($students->count() >= 1) {
            $this->command->info('Created: 1 student action plan');
        }
    }

    /**
     * Create sample drift scores for alignment dashboard.
     */
    protected function createDriftScores(Organization $org, StrategicPlan $plan): void
    {
        try {
            // Strong alignment examples (On Track)
            for ($i = 0; $i < 8; $i++) {
                StrategyDriftScore::create([
                    'org_id' => $org->id,
                    'strategic_plan_id' => $plan->id,
                    'contact_note_id' => null,
                    'alignment_score' => round(mt_rand(8500, 9800) / 10000, 4),
                    'alignment_level' => StrategyDriftScore::LEVEL_STRONG,
                    'matched_context' => [
                        ['type' => 'Goal', 'id' => 1, 'title' => 'Mental Health Support', 'similarity' => 0.92],
                        ['type' => 'KeyResult', 'id' => 1, 'title' => 'Counseling capacity', 'similarity' => 0.88],
                    ],
                    'drift_direction' => StrategyDriftScore::DIRECTION_STABLE,
                    'scored_by' => 'system',
                    'scored_at' => Carbon::now()->subDays(rand(1, 25)),
                ]);
            }

            // Moderate alignment examples (Drifting)
            for ($i = 0; $i < 5; $i++) {
                StrategyDriftScore::create([
                    'org_id' => $org->id,
                    'strategic_plan_id' => $plan->id,
                    'contact_note_id' => null,
                    'alignment_score' => round(mt_rand(6500, 8400) / 10000, 4),
                    'alignment_level' => StrategyDriftScore::LEVEL_MODERATE,
                    'matched_context' => [
                        ['type' => 'Goal', 'id' => 2, 'title' => 'Reduce Absenteeism', 'similarity' => 0.72],
                    ],
                    'drift_direction' => StrategyDriftScore::DIRECTION_DECLINING,
                    'scored_by' => 'system',
                    'scored_at' => Carbon::now()->subDays(rand(1, 25)),
                ]);
            }

            // Weak alignment examples (Off Track)
            for ($i = 0; $i < 3; $i++) {
                StrategyDriftScore::create([
                    'org_id' => $org->id,
                    'strategic_plan_id' => $plan->id,
                    'contact_note_id' => null,
                    'alignment_score' => round(mt_rand(3500, 6400) / 10000, 4),
                    'alignment_level' => StrategyDriftScore::LEVEL_WEAK,
                    'matched_context' => [
                        ['type' => 'Goal', 'id' => 3, 'title' => 'Family Communication', 'similarity' => 0.45],
                    ],
                    'drift_direction' => StrategyDriftScore::DIRECTION_DECLINING,
                    'scored_by' => 'system',
                    'scored_at' => Carbon::now()->subDays(rand(1, 25)),
                ]);
            }

            $this->command->info('Created: 16 alignment drift scores (8 on track, 5 drifting, 3 off track)');
        } catch (\Exception $e) {
            $this->command->warn('Could not create drift scores: '.$e->getMessage());
        }
    }
}
