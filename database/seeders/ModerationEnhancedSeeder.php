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

        $statuses = ['pending' => 8, 'flagged' => 5, 'needs_review' => 7, 'passed' => 10];
        $totalResults = 0;

        foreach ($statuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $course = $courses->random();

                ContentModerationResult::create([
                    'org_id' => $school->id,
                    'moderatable_type' => 'App\\Models\\MiniCourse',
                    'moderatable_id' => $course->id,
                    'status' => $status,
                    'overall_score' => rand(50, 100),
                    'toxicity_score' => rand(0, 30),
                    'quality_score' => rand(60, 100),
                    'confidence_score' => rand(70, 100),
                    'flagged_content' => $status === 'flagged' ? ['inappropriate language'] : null,
                    'reviewed_by' => $status === 'passed' ? $admin->id : null,
                    'reviewed_at' => $status === 'passed' ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
                $totalResults++;
            }
        }

        $this->command->info("Created {$totalResults} moderation results");
    }
}
