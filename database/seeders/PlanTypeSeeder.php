<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\KeyResult;
use App\Models\Milestone;
use App\Models\Organization;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use App\Models\StrategyCollaborator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->info('No school organization found. Skipping PlanTypeSeeder.');

            return;
        }

        $admin = User::where('org_id', $school->id)
            ->where('primary_role', 'admin')
            ->first();

        if (! $admin) {
            $admin = User::where('org_id', $school->id)->first();
        }

        if (! $admin) {
            $this->command->info('No admin user found. Skipping PlanTypeSeeder.');

            return;
        }

        $teachers = User::where('org_id', $school->id)
            ->where('primary_role', 'teacher')
            ->take(3)
            ->get();

        $this->command->info('Creating demo plans...');

        // Create one of each new plan type
        $this->createImprovementPlan($school, $admin, $teachers->first() ?? $admin);
        $this->createGrowthPlan($school, $teachers->first() ?? $admin);
        $this->createStrategicOkrPlan($school, $admin);
        $this->createActionPlan($school, $admin);

        $this->command->info('Demo plans created successfully!');
    }

    /**
     * Create a Performance Improvement Plan (PIP).
     */
    protected function createImprovementPlan(Organization $school, User $admin, User $teacher): void
    {
        $pip = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => 'Performance Improvement Plan - '.$teacher->first_name.' '.$teacher->last_name,
            'description' => 'Structured 90-day improvement plan addressing classroom management and student engagement.',
            'plan_type' => 'improvement',
            'category' => 'pip',
            'target_type' => 'App\\Models\\User',
            'target_id' => $teacher->id,
            'manager_id' => $admin->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->addDays(75),
            'created_by' => $admin->id,
        ]);

        // Add collaborators
        StrategyCollaborator::create([
            'strategic_plan_id' => $pip->id,
            'user_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Goal 1: Classroom Management
        $goal1 = Goal::create([
            'strategic_plan_id' => $pip->id,
            'title' => 'Improve Classroom Management',
            'description' => 'Reduce classroom disruptions and improve student time-on-task',
            'goal_type' => 'objective',
            'target_value' => 50,
            'current_value' => 20,
            'unit' => 'percent reduction in disruptions',
            'due_date' => Carbon::now()->addDays(75),
            'status' => 'in_progress',
            'sort_order' => 1,
            'owner_id' => $teacher->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Complete PBIS training modules',
            'metric_type' => 'number',
            'target_value' => 5,
            'current_value' => 2,
            'starting_value' => 0,
            'unit' => 'modules',
            'due_date' => Carbon::now()->addDays(30),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Implement daily behavior tracking system',
            'metric_type' => 'boolean',
            'target_value' => 1,
            'current_value' => 0,
            'starting_value' => 0,
            'due_date' => Carbon::now()->addDays(14),
            'status' => 'in_progress',
            'sort_order' => 2,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Reduce office referrals per week',
            'metric_type' => 'number',
            'target_value' => 2,
            'current_value' => 6,
            'starting_value' => 8,
            'unit' => 'referrals/week',
            'due_date' => Carbon::now()->addDays(60),
            'status' => 'at_risk',
            'sort_order' => 3,
        ]);

        // Goal 2: Student Engagement
        $goal2 = Goal::create([
            'strategic_plan_id' => $pip->id,
            'title' => 'Increase Student Engagement',
            'description' => 'Implement more interactive teaching strategies',
            'goal_type' => 'objective',
            'due_date' => Carbon::now()->addDays(75),
            'status' => 'not_started',
            'sort_order' => 2,
            'owner_id' => $teacher->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Incorporate 3 new engagement strategies per lesson',
            'metric_type' => 'percentage',
            'target_value' => 100,
            'current_value' => 25,
            'starting_value' => 0,
            'unit' => '%',
            'due_date' => Carbon::now()->addDays(45),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        // Milestones
        Milestone::create([
            'strategic_plan_id' => $pip->id,
            'goal_id' => $goal1->id,
            'title' => '30-Day Check-in Meeting',
            'description' => 'Review progress and adjust goals as needed',
            'due_date' => Carbon::now()->addDays(15),
            'status' => 'pending',
            'sort_order' => 1,
        ]);

        Milestone::create([
            'strategic_plan_id' => $pip->id,
            'title' => '60-Day Progress Review',
            'due_date' => Carbon::now()->addDays(45),
            'status' => 'pending',
            'sort_order' => 2,
        ]);

        Milestone::create([
            'strategic_plan_id' => $pip->id,
            'title' => 'Final 90-Day Evaluation',
            'due_date' => Carbon::now()->addDays(75),
            'status' => 'pending',
            'sort_order' => 3,
        ]);

        // Progress Updates
        ProgressUpdate::create([
            'strategic_plan_id' => $pip->id,
            'goal_id' => $goal1->id,
            'content' => 'Completed first two PBIS training modules. Starting to see improvements in morning routines.',
            'update_type' => 'manual',
            'created_by' => $teacher->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $pip->id,
            'content' => 'Met with instructional coach to develop new engagement strategies. Will implement starting next week.',
            'update_type' => 'manual',
            'created_by' => $teacher->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $this->command->info('  - Created PIP: '.$pip->title);
    }

    /**
     * Create a Growth/Development Plan (IDP).
     */
    protected function createGrowthPlan(Organization $school, User $teacher): void
    {
        $idp = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => 'Professional Growth Plan - '.$teacher->first_name.' '.$teacher->last_name,
            'description' => 'Self-directed professional development for instructional leadership and technology integration.',
            'plan_type' => 'growth',
            'category' => 'idp',
            'target_type' => 'App\\Models\\User',
            'target_id' => $teacher->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->addMonths(10),
            'created_by' => $teacher->id,
        ]);

        StrategyCollaborator::create([
            'strategic_plan_id' => $idp->id,
            'user_id' => $teacher->id,
            'role' => 'owner',
        ]);

        // Goal 1: Instructional Coaching Certification
        $goal1 = Goal::create([
            'strategic_plan_id' => $idp->id,
            'title' => 'Earn Instructional Coaching Certification',
            'description' => 'Complete all requirements for state instructional coaching certification',
            'goal_type' => 'objective',
            'due_date' => Carbon::now()->addMonths(6),
            'status' => 'in_progress',
            'sort_order' => 1,
            'owner_id' => $teacher->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Complete certification coursework',
            'metric_type' => 'percentage',
            'target_value' => 100,
            'current_value' => 45,
            'starting_value' => 0,
            'unit' => '%',
            'due_date' => Carbon::now()->addMonths(4),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal1->id,
            'title' => 'Complete required coaching hours',
            'metric_type' => 'number',
            'target_value' => 40,
            'current_value' => 12,
            'starting_value' => 0,
            'unit' => 'hours',
            'due_date' => Carbon::now()->addMonths(5),
            'status' => 'in_progress',
            'sort_order' => 2,
        ]);

        // Goal 2: Technology Integration
        $goal2 = Goal::create([
            'strategic_plan_id' => $idp->id,
            'title' => 'Master Educational Technology Tools',
            'description' => 'Become proficient in modern EdTech tools for classroom instruction',
            'goal_type' => 'objective',
            'due_date' => Carbon::now()->addMonths(8),
            'status' => 'in_progress',
            'sort_order' => 2,
            'owner_id' => $teacher->id,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Complete Google Educator Level 2 certification',
            'metric_type' => 'boolean',
            'target_value' => 1,
            'current_value' => 0,
            'starting_value' => 0,
            'due_date' => Carbon::now()->addMonths(3),
            'status' => 'not_started',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal2->id,
            'title' => 'Lead 2 professional development sessions on EdTech',
            'metric_type' => 'number',
            'target_value' => 2,
            'current_value' => 0,
            'starting_value' => 0,
            'unit' => 'sessions',
            'due_date' => Carbon::now()->addMonths(7),
            'status' => 'not_started',
            'sort_order' => 2,
        ]);

        // Milestones
        Milestone::create([
            'strategic_plan_id' => $idp->id,
            'goal_id' => $goal1->id,
            'title' => 'Submit certification application',
            'due_date' => Carbon::now()->addMonths(5),
            'status' => 'pending',
            'sort_order' => 1,
        ]);

        Milestone::create([
            'strategic_plan_id' => $idp->id,
            'title' => 'Quarterly reflection meeting with mentor',
            'due_date' => Carbon::now()->addMonths(1),
            'status' => 'pending',
            'sort_order' => 2,
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $idp->id,
            'goal_id' => $goal1->id,
            'content' => 'Finished Module 4 of coaching coursework. Very excited about the observation techniques learned!',
            'update_type' => 'manual',
            'created_by' => $teacher->id,
            'created_at' => Carbon::now()->subDays(7),
        ]);

        $this->command->info('  - Created IDP: '.$idp->title);
    }

    /**
     * Create a Strategic OKR Plan.
     */
    protected function createStrategicOkrPlan(Organization $school, User $admin): void
    {
        $okr = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => 'Q1 2026 School OKRs - Student Success Initiative',
            'description' => 'Quarterly objectives and key results focused on improving student outcomes and engagement across all grade levels.',
            'plan_type' => 'strategic',
            'category' => 'okr',
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
            'end_date' => Carbon::create(2026, 3, 31),
            'created_by' => $admin->id,
        ]);

        StrategyCollaborator::create([
            'strategic_plan_id' => $okr->id,
            'user_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Objective 1: Student Engagement
        $obj1 = Goal::create([
            'strategic_plan_id' => $okr->id,
            'title' => 'Increase Student Engagement Across All Grades',
            'description' => 'Create more engaging learning environments leading to improved attendance and participation',
            'goal_type' => 'objective',
            'due_date' => Carbon::create(2026, 3, 31),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $obj1->id,
            'title' => 'Improve average daily attendance rate',
            'metric_type' => 'percentage',
            'starting_value' => 92,
            'target_value' => 96,
            'current_value' => 93.5,
            'unit' => '%',
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $obj1->id,
            'title' => 'Increase student participation in extracurricular activities',
            'metric_type' => 'percentage',
            'starting_value' => 45,
            'target_value' => 60,
            'current_value' => 52,
            'unit' => '%',
            'status' => 'on_track',
            'sort_order' => 2,
        ]);

        KeyResult::create([
            'goal_id' => $obj1->id,
            'title' => 'Reduce chronic absenteeism rate',
            'metric_type' => 'percentage',
            'starting_value' => 12,
            'target_value' => 8,
            'current_value' => 10,
            'unit' => '%',
            'status' => 'in_progress',
            'sort_order' => 3,
        ]);

        // Objective 2: Academic Achievement
        $obj2 = Goal::create([
            'strategic_plan_id' => $okr->id,
            'title' => 'Improve Academic Achievement in Core Subjects',
            'description' => 'Raise proficiency levels in math and ELA across all tested grades',
            'goal_type' => 'objective',
            'due_date' => Carbon::create(2026, 3, 31),
            'status' => 'in_progress',
            'sort_order' => 2,
        ]);

        KeyResult::create([
            'goal_id' => $obj2->id,
            'title' => 'Increase math proficiency rate',
            'metric_type' => 'percentage',
            'starting_value' => 65,
            'target_value' => 75,
            'current_value' => 68,
            'unit' => '%',
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $obj2->id,
            'title' => 'Increase ELA proficiency rate',
            'metric_type' => 'percentage',
            'starting_value' => 70,
            'target_value' => 80,
            'current_value' => 73,
            'unit' => '%',
            'status' => 'in_progress',
            'sort_order' => 2,
        ]);

        // Objective 3: Teacher Development
        $obj3 = Goal::create([
            'strategic_plan_id' => $okr->id,
            'title' => 'Strengthen Instructional Quality',
            'description' => 'Invest in teacher professional development and peer collaboration',
            'goal_type' => 'objective',
            'due_date' => Carbon::create(2026, 3, 31),
            'status' => 'in_progress',
            'sort_order' => 3,
        ]);

        KeyResult::create([
            'goal_id' => $obj3->id,
            'title' => 'Complete professional development hours per teacher',
            'metric_type' => 'number',
            'starting_value' => 0,
            'target_value' => 20,
            'current_value' => 8,
            'unit' => 'hours',
            'status' => 'on_track',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $obj3->id,
            'title' => 'Peer observation cycles completed',
            'metric_type' => 'number',
            'starting_value' => 0,
            'target_value' => 3,
            'current_value' => 1,
            'unit' => 'cycles',
            'status' => 'on_track',
            'sort_order' => 2,
        ]);

        // Milestones
        Milestone::create([
            'strategic_plan_id' => $okr->id,
            'title' => 'Mid-Quarter OKR Review',
            'due_date' => Carbon::create(2026, 2, 15),
            'status' => 'pending',
            'sort_order' => 1,
        ]);

        Milestone::create([
            'strategic_plan_id' => $okr->id,
            'title' => 'End of Quarter Assessment Window',
            'due_date' => Carbon::create(2026, 3, 20),
            'status' => 'pending',
            'sort_order' => 2,
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $okr->id,
            'goal_id' => $obj1->id,
            'content' => 'Launched new attendance incentive program this week. Early indicators show positive response from students.',
            'update_type' => 'manual',
            'created_by' => $admin->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $this->command->info('  - Created OKR: '.$okr->title);
    }

    /**
     * Create an Action Plan triggered by an alert.
     */
    protected function createActionPlan(Organization $school, User $admin): void
    {
        $actionPlan = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => 'Crisis Response: 9th Grade Attendance Drop',
            'description' => 'Immediate action plan triggered by attendance alert. Grade 9 attendance dropped below 85% threshold.',
            'plan_type' => 'action',
            'category' => 'action_plan',
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addWeeks(4),
            'created_by' => $admin->id,
            'metadata' => [
                'trigger_type' => 'alert',
                'alert_metric' => 'attendance',
                'threshold_breached' => '85%',
                'affected_grade' => 9,
                'severity' => 'high',
            ],
        ]);

        StrategyCollaborator::create([
            'strategic_plan_id' => $actionPlan->id,
            'user_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Single focused goal
        $goal = Goal::create([
            'strategic_plan_id' => $actionPlan->id,
            'title' => 'Restore Grade 9 Attendance to Target Level',
            'description' => 'Implement immediate interventions to address attendance crisis',
            'goal_type' => 'objective',
            'target_value' => 90,
            'current_value' => 82,
            'unit' => '% attendance',
            'due_date' => Carbon::now()->addWeeks(4),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal->id,
            'title' => 'Contact families of chronically absent students',
            'metric_type' => 'percentage',
            'target_value' => 100,
            'current_value' => 40,
            'starting_value' => 0,
            'unit' => '%',
            'due_date' => Carbon::now()->addWeeks(1),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        KeyResult::create([
            'goal_id' => $goal->id,
            'title' => 'Students connected with support services',
            'metric_type' => 'number',
            'target_value' => 15,
            'current_value' => 6,
            'starting_value' => 0,
            'unit' => 'students',
            'due_date' => Carbon::now()->addWeeks(2),
            'status' => 'in_progress',
            'sort_order' => 2,
        ]);

        // Immediate action milestones
        Milestone::create([
            'strategic_plan_id' => $actionPlan->id,
            'title' => 'Complete family outreach for all at-risk students',
            'due_date' => Carbon::now()->addDays(3),
            'status' => 'in_progress',
            'sort_order' => 1,
        ]);

        Milestone::create([
            'strategic_plan_id' => $actionPlan->id,
            'title' => 'Launch morning check-in program',
            'due_date' => Carbon::now()->addWeeks(1),
            'status' => 'pending',
            'sort_order' => 2,
        ]);

        Milestone::create([
            'strategic_plan_id' => $actionPlan->id,
            'title' => 'Implement peer mentorship pairing',
            'due_date' => Carbon::now()->addWeeks(2),
            'status' => 'pending',
            'sort_order' => 3,
        ]);

        Milestone::create([
            'strategic_plan_id' => $actionPlan->id,
            'title' => 'Progress review with counseling team',
            'due_date' => Carbon::now()->addWeeks(3),
            'status' => 'pending',
            'sort_order' => 4,
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $actionPlan->id,
            'content' => 'Alert triggered: Grade 9 attendance dropped to 82% (threshold: 85%). Initiating crisis response protocol.',
            'update_type' => 'system',
            'created_by' => $admin->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $actionPlan->id,
            'goal_id' => $goal->id,
            'content' => 'Called 8 families today. Identified transportation issues as common barrier. Coordinating with district transportation office.',
            'update_type' => 'manual',
            'created_by' => $admin->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $actionPlan->id,
            'content' => 'Met with counseling team to identify students needing additional support. 6 students connected with services so far.',
            'update_type' => 'manual',
            'created_by' => $admin->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $this->command->info('  - Created Action Plan: '.$actionPlan->title);
    }
}
