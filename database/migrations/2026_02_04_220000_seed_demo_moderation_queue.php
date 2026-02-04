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

            // Get a user for this org
            $user = DB::table('users')->where('org_id', $org->id)->first();
            if (! $user) {
                continue;
            }

            // Create multiple demo mini courses for different moderation scenarios
            $courses = [];

            // Course 1: Flagged - Clinical Safety Concern
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Managing Test Anxiety',
                'description' => 'A course helping students cope with test anxiety through breathing techniques and mindset shifts.',
                'course_type' => 'skill_building',
                'estimated_duration_minutes' => 25,
                'status' => 'draft',
                'approval_status' => 'pending_review',
                'created_by' => $user->id,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);

            // Course 2: Flagged - Cultural Sensitivity
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Building Study Habits',
                'description' => 'Learn effective study techniques including time management and note-taking strategies.',
                'course_type' => 'skill_building',
                'estimated_duration_minutes' => 30,
                'status' => 'draft',
                'approval_status' => 'pending_review',
                'created_by' => $user->id,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ]);

            // Course 3: Rejected
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Dealing with Peer Pressure',
                'description' => 'Understanding and resisting negative peer influences in school settings.',
                'course_type' => 'social_emotional',
                'estimated_duration_minutes' => 20,
                'status' => 'draft',
                'approval_status' => 'rejected',
                'created_by' => $user->id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ]);

            // Course 4: Passed
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Introduction to Growth Mindset',
                'description' => 'Discover how adopting a growth mindset can improve academic performance and resilience.',
                'course_type' => 'skill_building',
                'estimated_duration_minutes' => 15,
                'status' => 'active',
                'approval_status' => 'approved',
                'created_by' => $user->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ]);

            // Course 5: Approved Override
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Digital Citizenship Basics',
                'description' => 'Learn about responsible online behavior, privacy, and digital footprint management.',
                'course_type' => 'skill_building',
                'estimated_duration_minutes' => 20,
                'status' => 'active',
                'approval_status' => 'approved',
                'created_by' => $user->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ]);

            // Course 6: Pending (not yet reviewed by AI)
            $courses[] = DB::table('mini_courses')->insertGetId([
                'org_id' => $org->id,
                'title' => 'Conflict Resolution Skills',
                'description' => 'Practical strategies for resolving conflicts peacefully with peers and adults.',
                'course_type' => 'social_emotional',
                'estimated_duration_minutes' => 25,
                'status' => 'draft',
                'approval_status' => 'pending_review',
                'created_by' => $user->id,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]);

            // =====================================================
            // CREATE MODERATION RESULTS FOR EACH STATUS
            // =====================================================

            // 1. FLAGGED - Clinical Safety Concern (needs review)
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[0],
                'status' => 'flagged',
                'overall_score' => 0.72,
                'age_appropriateness_score' => 0.85,
                'clinical_safety_score' => 0.58,
                'cultural_sensitivity_score' => 0.82,
                'accuracy_score' => 0.80,
                'flags' => json_encode([
                    'Clinical safety concern: Content discusses anxiety symptoms without professional disclaimers',
                    'Recommendation to seek professional help should be more prominent',
                ]),
                'recommendations' => json_encode([
                    'Add disclaimer that this is educational content, not therapy',
                    'Include information about when to seek professional help',
                    'Review language around "coping mechanisms" for clinical accuracy',
                ]),
                'human_reviewed' => false,
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]);

            // 2. FLAGGED - Cultural Sensitivity (needs review)
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[1],
                'status' => 'flagged',
                'overall_score' => 0.68,
                'age_appropriateness_score' => 0.90,
                'clinical_safety_score' => 0.85,
                'cultural_sensitivity_score' => 0.48,
                'accuracy_score' => 0.75,
                'flags' => json_encode([
                    'Cultural sensitivity: Study techniques assume access to quiet private space',
                    'Examples may not reflect diverse family structures and living situations',
                ]),
                'recommendations' => json_encode([
                    'Include alternative study environments for students without private spaces',
                    'Add examples that reflect diverse family structures',
                    'Consider students who may have caregiving responsibilities',
                ]),
                'human_reviewed' => false,
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ]);

            // 3. REJECTED - Multiple serious concerns
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[2],
                'status' => 'rejected',
                'overall_score' => 0.35,
                'age_appropriateness_score' => 0.45,
                'clinical_safety_score' => 0.30,
                'cultural_sensitivity_score' => 0.40,
                'accuracy_score' => 0.38,
                'flags' => json_encode([
                    'Age appropriateness: Scenarios too mature for younger K-12 students',
                    'Clinical safety: Discusses substance use without appropriate safeguards',
                    'Content accuracy: Some advice contradicts evidence-based approaches',
                ]),
                'recommendations' => json_encode([
                    'Significant revision needed - consult with school counselor',
                    'Remove or reframe substance-related scenarios',
                    'Ensure alignment with evidence-based peer pressure resistance strategies',
                ]),
                'human_reviewed' => false,
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);

            // 4. PASSED - All scores good
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[3],
                'status' => 'passed',
                'overall_score' => 0.92,
                'age_appropriateness_score' => 0.95,
                'clinical_safety_score' => 0.90,
                'cultural_sensitivity_score' => 0.88,
                'accuracy_score' => 0.94,
                'flags' => json_encode([]),
                'recommendations' => json_encode([]),
                'human_reviewed' => false,
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ]);

            // 5. APPROVED_OVERRIDE - Was flagged, human approved
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[4],
                'status' => 'approved_override',
                'overall_score' => 0.74,
                'age_appropriateness_score' => 0.88,
                'clinical_safety_score' => 0.82,
                'cultural_sensitivity_score' => 0.65,
                'accuracy_score' => 0.78,
                'flags' => json_encode([
                    'Minor cultural sensitivity note: Some examples US-centric',
                ]),
                'recommendations' => json_encode([
                    'Consider adding international social media platform examples',
                ]),
                'human_reviewed' => true,
                'reviewed_by' => $user->id,
                'reviewed_at' => now()->subDays(2),
                'review_notes' => 'Reviewed and approved. The US-centric examples are appropriate for our student population. Will consider updates in future revision.',
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ]);

            // 6. PENDING - Just submitted, awaiting AI review
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[5],
                'status' => 'pending',
                'overall_score' => null,
                'age_appropriateness_score' => null,
                'clinical_safety_score' => null,
                'cultural_sensitivity_score' => null,
                'accuracy_score' => null,
                'flags' => json_encode([]),
                'recommendations' => json_encode([]),
                'human_reviewed' => false,
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]);

            // 7. Extra FLAGGED with assignment details (to show assignment workflow)
            DB::table('content_moderation_results')->insert([
                'org_id' => $org->id,
                'moderatable_type' => 'App\\Models\\MiniCourse',
                'moderatable_id' => $courses[0],
                'status' => 'flagged',
                'overall_score' => 0.71,
                'age_appropriateness_score' => 0.78,
                'clinical_safety_score' => 0.62,
                'cultural_sensitivity_score' => 0.75,
                'accuracy_score' => 0.72,
                'flags' => json_encode([
                    'Urgent: Review needed for anxiety management techniques',
                ]),
                'recommendations' => json_encode([
                    'Verify breathing exercise instructions are safe',
                    'Add note about when anxiety requires professional intervention',
                ]),
                'human_reviewed' => false,
                'assigned_to' => $user->id,
                'assigned_by' => $user->id,
                'assigned_at' => now()->subHours(1),
                'assignment_priority' => 'high',
                'due_at' => now()->addHours(4),
                'assignment_notes' => 'Please review the anxiety-related content carefully. This is for our upcoming wellness week.',
                'model_version' => 'demo-seed-v1',
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(1),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete demo moderation results
        DB::table('content_moderation_results')
            ->where('model_version', 'demo-seed-v1')
            ->delete();

        // Delete demo courses
        DB::table('mini_courses')
            ->whereIn('title', [
                'Managing Test Anxiety',
                'Building Study Habits',
                'Dealing with Peer Pressure',
                'Introduction to Growth Mindset',
                'Digital Citizenship Basics',
                'Conflict Resolution Skills',
            ])
            ->delete();
    }
};
