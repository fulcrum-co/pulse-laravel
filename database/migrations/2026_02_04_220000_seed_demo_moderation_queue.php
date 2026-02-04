<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all organizations
        $orgs = DB::table('organizations')->get();

        foreach ($orgs as $org) {
            // Check if moderation results already exist for this org
            $existing = DB::table('content_moderation_results')->where('org_id', $org->id)->count();
            if ($existing > 0) {
                continue;
            }

            // Try to find a mini course to moderate, or create one
            $course = DB::table('mini_courses')->where('org_id', $org->id)->first();

            if (! $course) {
                // Create a demo mini course
                $user = DB::table('users')->where('org_id', $org->id)->first();
                if (! $user) {
                    continue;
                }

                $courseId = DB::table('mini_courses')->insertGetId([
                    'org_id' => $org->id,
                    'title' => 'Demo Course: Study Skills Workshop',
                    'description' => 'A demo course created for moderation testing. This course covers essential study skills for academic success.',
                    'course_type' => 'skill_building',
                    'estimated_duration_minutes' => 30,
                    'status' => 'draft',
                    'approval_status' => 'pending_review',
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $courseId = $course->id;
            }

            // Create moderation results for testing
            // 1. Flagged item (needs review)
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courseId,
                'status' => 'flagged',
                'overall_score' => 0.72,
                'age_appropriateness_score' => 0.85,
                'clinical_safety_score' => 0.65,
                'cultural_sensitivity_score' => 0.78,
                'accuracy_score' => 0.80,
                'flags' => json_encode(['Potential clinical safety concern - review language around stress management']),
                'recommendations' => json_encode(['Consider softening clinical language', 'Add disclaimer for educational purposes']),
                'human_reviewed' => false,
                'model_version' => 'claude-3-sonnet',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]);

            // 2. Another flagged item
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courseId,
                'status' => 'flagged',
                'overall_score' => 0.68,
                'age_appropriateness_score' => 0.70,
                'clinical_safety_score' => 0.72,
                'cultural_sensitivity_score' => 0.65,
                'accuracy_score' => 0.75,
                'flags' => json_encode(['Cultural sensitivity review needed - content may not be inclusive']),
                'recommendations' => json_encode(['Include diverse examples', 'Review for inclusive language']),
                'human_reviewed' => false,
                'model_version' => 'claude-3-sonnet',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ]);

            // 3. Passed item (for history)
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courseId,
                'status' => 'passed',
                'overall_score' => 0.92,
                'age_appropriateness_score' => 0.95,
                'clinical_safety_score' => 0.90,
                'cultural_sensitivity_score' => 0.88,
                'accuracy_score' => 0.94,
                'flags' => json_encode([]),
                'recommendations' => json_encode([]),
                'human_reviewed' => false,
                'model_version' => 'claude-3-sonnet',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only delete demo content - be careful not to delete real data
        DB::table('content_moderation_results')
            ->where('model_version', 'claude-3-sonnet')
            ->whereIn('overall_score', [0.72, 0.68, 0.92])
            ->delete();
    }
};
