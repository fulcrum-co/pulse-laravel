<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\StrategicPlan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StrategicPlanEnhancedSeeder extends Seeder
{
    /**
     * Create 15-20 intervention plans for high-risk students.
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();

        // Get high-risk students (should be 15 based on ContactEnhancedSeeder)
        $highRiskStudents = Student::where('org_id', $school->id)
            ->where('risk_level', 'high')
            ->get();

        if ($highRiskStudents->isEmpty()) {
            $this->command->warn('No high-risk students found. Using all students.');
            $highRiskStudents = Student::where('org_id', $school->id)->take(15)->get();
        }

        $planTitles = [
            'Academic Intervention Plan',
            'Attendance Improvement Plan',
            'Behavioral Support Plan',
            'Credit Recovery Plan',
            'Engagement Boost Initiative',
        ];

        $categories = ['academic', 'attendance', 'behavior', 'wellness', 'engagement'];
        $totalPlans = 0;

        foreach ($highRiskStudents as $student) {
            // Create 1-2 plans per high-risk student
            $numPlans = rand(1, 2);

            for ($i = 0; $i < $numPlans; $i++) {
                $category = $categories[array_rand($categories)];
                $title = $planTitles[array_rand($planTitles)];

                $startDate = now()->subDays(rand(30, 90));
                $endDate = $startDate->copy()->addDays(rand(60, 180));

                StrategicPlan::create([
                    'org_id' => $school->id,
                    'title' => $title . ' - ' . ($student->user->first_name ?? 'Student'),
                    'description' => "Intervention plan to address {$category} concerns and support student success.",
                    'plan_type' => StrategicPlan::TYPE_STUDENT ?? 'student',
                    'category' => $category,
                    'target_type' => 'App\\Models\\Student',
                    'target_id' => $student->id,
                    'status' => $this->weightedRandom([
                        'active' => 60,
                        'completed' => 30,
                        'draft' => 10,
                    ]),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'consultant_visible' => true,
                    'settings' => [
                        'check_in_frequency' => 'weekly',
                        'progress_metrics' => [$category . '_score', 'engagement_level'],
                    ],
                    'metadata' => [
                        'risk_level' => $student->risk_level,
                        'priority' => 'high',
                    ],
                    'created_by' => $admin->id,
                    'manager_id' => $admin->id,
                    'created_at' => $startDate,
                    'updated_at' => $startDate->copy()->addDays(rand(1, 30)),
                ]);

                $totalPlans++;
            }
        }

        $this->command->info("Created {$totalPlans} intervention plans for {$highRiskStudents->count()} high-risk students");
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $sum = 0;
        foreach ($weights as $key => $weight) {
            $sum += $weight;
            if ($random <= $sum) return $key;
        }
        return array_key_first($weights);
    }
}
