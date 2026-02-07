<?php

namespace Database\Seeders;

use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModerationEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();
        $courses = MiniCourse::where('org_id', $school->id)->take(25)->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found for moderation. Skipping.');
            return;
        }

        $statuses = ['pending' => 8, 'flagged' => 5, 'approved_override' => 7, 'passed' => 10];
        $totalResults = 0;

        foreach ($statuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $course = $courses->random();

                // Scores are 0.0-1.0 (higher is better/safer)
                $overallScore = $status === 'flagged' ? rand(3000, 6000) / 10000 : rand(7000, 9500) / 10000;

                ContentModerationResult::create([
                    'org_id' => $school->id,
                    'moderatable_type' => 'App\\Models\\MiniCourse',
                    'moderatable_id' => $course->id,
                    'status' => $status,
                    'overall_score' => $overallScore,
                    'age_appropriateness_score' => $status === 'flagged' ? rand(5000, 7000) / 10000 : rand(8000, 10000) / 10000,
                    'clinical_safety_score' => $status === 'flagged' ? rand(4000, 6000) / 10000 : rand(7500, 10000) / 10000,
                    'cultural_sensitivity_score' => rand(7000, 10000) / 10000,
                    'accuracy_score' => rand(7500, 10000) / 10000,
                    'flags' => $status === 'flagged' ? ['Minor content concern', 'Needs expert review'] : null,
                    'recommendations' => $status === 'flagged' ? ['Review language for age appropriateness', 'Add clinical references'] : null,
                    'human_reviewed' => in_array($status, ['passed', 'approved_override']),
                    'reviewed_by' => in_array($status, ['passed', 'approved_override']) ? $admin->id : null,
                    'reviewed_at' => in_array($status, ['passed', 'approved_override']) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
                $totalResults++;
            }
        }

        $this->command->info("Created {$totalResults} moderation results");
    }
}
