<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StrategicPlan;
use App\Models\FocusArea;
use App\Models\Objective;
use App\Models\Activity;
use App\Models\StrategyCollaborator;
use App\Models\StrategyAssignment;
use App\Models\Organization;
use App\Models\User;
use App\Models\Student;
use Carbon\Carbon;

class StrategySeeder extends Seeder
{
    public function run(): void
    {
        // Get the first school organization
        $school = Organization::where('org_type', 'school')->first();

        if (!$school) {
            $this->command->warn('No school organization found. Please run OrganizationSeeder first.');
            return;
        }

        // Get users
        $admin = User::where('org_id', $school->id)->where('primary_role', 'admin')->first();
        $teachers = User::where('org_id', $school->id)->where('primary_role', 'teacher')->get();
        $students = Student::where('org_id', $school->id)->get();

        if (!$admin) {
            $this->command->warn('No admin user found. Please run UserSeeder first.');
            return;
        }

        // Create a 5-Year Strategic Plan
        $fiveYearPlan = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => 'Lincoln High School 5-Year Strategic Plan',
            'description' => 'Comprehensive strategic plan to improve student wellness and academic outcomes over the next five years.',
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

        // Add teachers as collaborators
        foreach ($teachers->take(2) as $teacher) {
            StrategyCollaborator::create([
                'strategic_plan_id' => $fiveYearPlan->id,
                'user_id' => $teacher->id,
                'role' => 'collaborator',
            ]);
        }

        // Focus Area 1: Student Mental Health & Wellness
        $fa1 = FocusArea::create([
            'strategic_plan_id' => $fiveYearPlan->id,
            'title' => 'Student Mental Health & Wellness',
            'description' => 'Improve overall student mental health and create a supportive school environment.',
            'sort_order' => 0,
            'status' => 'on_track',
        ]);

        $obj1_1 = Objective::create([
            'focus_area_id' => $fa1->id,
            'title' => 'Implement universal mental health screening',
            'description' => 'Screen all students for mental health concerns twice per year.',
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
            'title' => 'Hire two additional counselors',
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
            'description' => 'Improve academic outcomes for all students.',
            'sort_order' => 1,
            'status' => 'at_risk',
        ]);

        $obj2_1 = Objective::create([
            'focus_area_id' => $fa2->id,
            'title' => 'Reduce achievement gaps',
            'description' => 'Close achievement gaps between student populations.',
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
            'title' => 'Expand after-school academic support',
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
            'title' => 'Increase parent involvement',
            'start_date' => Carbon::now()->addMonths(6),
            'end_date' => Carbon::now()->addYear(),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        Activity::create([
            'objective_id' => $obj3_1->id,
            'title' => 'Launch parent communication app',
            'start_date' => Carbon::now()->addMonths(6),
            'end_date' => Carbon::now()->addMonths(8),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        Activity::create([
            'objective_id' => $obj3_1->id,
            'title' => 'Establish monthly parent workshops',
            'start_date' => Carbon::now()->addMonths(8),
            'end_date' => Carbon::now()->addYear(),
            'sort_order' => 1,
            'status' => 'not_started',
        ]);

        // Create a shorter Annual Plan
        $annualPlan = StrategicPlan::create([
            'org_id' => $school->id,
            'title' => '2025-2026 School Year Plan',
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
            'title' => 'Monthly wellness surveys for all students',
            'start_date' => Carbon::create(2025, 9, 1),
            'end_date' => Carbon::create(2026, 5, 31),
            'sort_order' => 0,
            'status' => 'not_started',
        ]);

        // Create Teacher Improvement Plans
        if ($teachers->count() >= 2) {
            $teacherPlan = StrategicPlan::create([
                'org_id' => $school->id,
                'title' => 'Professional Growth Plan - ' . $teachers[0]->first_name . ' ' . $teachers[0]->last_name,
                'description' => 'Individual improvement plan for classroom management and student engagement.',
                'plan_type' => 'teacher',
                'target_type' => 'App\\Models\\User',
                'target_id' => $teachers[0]->id,
                'status' => 'active',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYear(),
                'created_by' => $admin->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $teacherPlan->id,
                'user_id' => $admin->id,
                'role' => 'owner',
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $teacherPlan->id,
                'user_id' => $teachers[0]->id,
                'role' => 'collaborator',
            ]);

            $fa_teacher = FocusArea::create([
                'strategic_plan_id' => $teacherPlan->id,
                'title' => 'Classroom Management',
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            $obj_teacher = Objective::create([
                'focus_area_id' => $fa_teacher->id,
                'title' => 'Implement positive behavior interventions',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_teacher->id,
                'title' => 'Complete PBIS training',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_teacher->id,
                'title' => 'Implement reward system in classroom',
                'start_date' => Carbon::now()->addMonths(2),
                'end_date' => Carbon::now()->addMonths(4),
                'sort_order' => 1,
                'status' => 'not_started',
            ]);
        }

        // Create Student Improvement Plans
        if ($students->count() >= 3) {
            // High-risk student plan
            $highRiskStudent = $students->where('risk_level', 'high')->first() ?? $students->first();

            $studentPlan = StrategicPlan::create([
                'org_id' => $school->id,
                'title' => 'Student Support Plan - ' . $highRiskStudent->user->first_name . ' ' . $highRiskStudent->user->last_name,
                'description' => 'Individualized support plan addressing social-emotional needs.',
                'plan_type' => 'student',
                'target_type' => 'App\\Models\\Student',
                'target_id' => $highRiskStudent->id,
                'status' => 'active',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(4),
                'created_by' => $admin->id,
            ]);

            StrategyCollaborator::create([
                'strategic_plan_id' => $studentPlan->id,
                'user_id' => $admin->id,
                'role' => 'owner',
            ]);

            if ($teachers->count() > 0) {
                StrategyCollaborator::create([
                    'strategic_plan_id' => $studentPlan->id,
                    'user_id' => $teachers[0]->id,
                    'role' => 'collaborator',
                ]);
            }

            $fa_student = FocusArea::create([
                'strategic_plan_id' => $studentPlan->id,
                'title' => 'Emotional Regulation',
                'sort_order' => 0,
                'status' => 'at_risk',
            ]);

            $obj_student = Objective::create([
                'focus_area_id' => $fa_student->id,
                'title' => 'Develop coping strategies',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'at_risk',
            ]);

            Activity::create([
                'objective_id' => $obj_student->id,
                'title' => 'Weekly counseling sessions',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'on_track',
            ]);

            Activity::create([
                'objective_id' => $obj_student->id,
                'title' => 'Mindfulness practice training',
                'start_date' => Carbon::now()->addWeeks(2),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 1,
                'status' => 'at_risk',
            ]);

            $fa_student2 = FocusArea::create([
                'strategic_plan_id' => $studentPlan->id,
                'title' => 'Academic Support',
                'sort_order' => 1,
                'status' => 'not_started',
            ]);

            $obj_student2 = Objective::create([
                'focus_area_id' => $fa_student2->id,
                'title' => 'Improve homework completion',
                'start_date' => Carbon::now()->addMonths(1),
                'end_date' => Carbon::now()->addMonths(3),
                'sort_order' => 0,
                'status' => 'not_started',
            ]);

            Activity::create([
                'objective_id' => $obj_student2->id,
                'title' => 'Set up after-school study sessions',
                'start_date' => Carbon::now()->addMonths(1),
                'end_date' => Carbon::now()->addMonths(2),
                'sort_order' => 0,
                'status' => 'not_started',
            ]);
        }

        $this->command->info('Strategic plans seeded successfully!');
        $this->command->info('Created: 1 5-year plan, 1 annual plan, 1 teacher plan, 1 student plan');
    }
}
