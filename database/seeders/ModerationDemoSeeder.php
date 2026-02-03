<?php

namespace Database\Seeders;

use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModerationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('org_type', 'organization')->first();
        if (! $organization) {
            $organization = Organization::first();
        }
        if (! $organization) {
            $this->command->error('No organization found. Please seed organizations first.');

            return;
        }

        $admin = User::where('org_id', $organization->id)->first();
        if (! $admin) {
            $admin = User::first();
        }
        if (! $admin) {
            $this->command->error('No user found. Please seed users first.');

            return;
        }

        // Get eligible moderators for assignment demo
        $moderators = User::where('org_id', $organization->id)
            ->whereIn('primary_role', ['admin', 'counselor', 'organization_admin'])
            ->limit(3)
            ->get();

        $this->command->info('Creating demo moderation results...');

        // Demo course data - AI-generated courses that need moderation
        $demoCourses = [
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'Learn evidence-based techniques to overcome test anxiety and perform your best on exams. This course covers breathing exercises, cognitive reframing, and preparation strategies.',
                'status' => ContentModerationResult::STATUS_FLAGGED,
                'score' => 0.72,
                'flags' => ['clinical_terminology_review' => 'Contains references to anxiety symptoms that should be reviewed'],
                'priority' => 'high',
            ],
            [
                'title' => 'Building Healthy Friendships',
                'description' => 'Discover how to build meaningful relationships, set boundaries, and be a supportive friend. Perfect for learners navigating social dynamics.',
                'status' => ContentModerationResult::STATUS_PASSED,
                'score' => 0.92,
                'flags' => [],
                'priority' => 'normal',
            ],
            [
                'title' => 'Coping with Academic Pressure',
                'description' => 'When grades feel overwhelming, this course teaches practical strategies for managing academic stress while maintaining well-being.',
                'status' => ContentModerationResult::STATUS_FLAGGED,
                'score' => 0.68,
                'flags' => [
                    'age_appropriateness' => 'Some concepts may be advanced for younger grades',
                    'clinical_safety' => 'Mentions of stress symptoms should include disclaimers',
                ],
                'priority' => 'urgent',
            ],
            [
                'title' => 'Digital Wellness for Teens',
                'description' => 'Navigate social media, screen time, and online relationships in healthy ways. Learn to recognize digital burnout and create balance.',
                'status' => ContentModerationResult::STATUS_PENDING,
                'score' => 0.75,
                'flags' => ['content_review' => 'Pending initial review'],
                'priority' => 'normal',
            ],
            [
                'title' => 'Understanding Your Emotions',
                'description' => 'A foundational course on emotional intelligence, helping learners identify, understand, and express their feelings appropriately.',
                'status' => ContentModerationResult::STATUS_REJECTED,
                'score' => 0.35,
                'flags' => [
                    'clinical_safety' => 'Contains advice that could be misinterpreted without professional guidance',
                    'accuracy' => 'Some psychological concepts oversimplified incorrectly',
                    'age_appropriateness' => 'Topics too advanced for target audience',
                ],
                'priority' => 'high',
            ],
            [
                'title' => 'Time Management Essentials',
                'description' => 'Master the art of managing your time effectively. Learn prioritization, scheduling, and how to balance organization, activities, and rest.',
                'status' => ContentModerationResult::STATUS_PASSED,
                'score' => 0.95,
                'flags' => [],
                'priority' => 'low',
            ],
            [
                'title' => 'Mindfulness for Learners',
                'description' => 'Introduction to mindfulness practices including breathing exercises, body scans, and present-moment awareness techniques.',
                'status' => ContentModerationResult::STATUS_FLAGGED,
                'score' => 0.78,
                'flags' => ['cultural_sensitivity' => 'May benefit from more inclusive language around meditation practices'],
                'priority' => 'normal',
            ],
            [
                'title' => 'Navigating Family Changes',
                'description' => 'Support for learners dealing with family transitions like divorce, moving, or new siblings. Focuses on emotional processing and adaptation.',
                'status' => ContentModerationResult::STATUS_FLAGGED,
                'score' => 0.65,
                'flags' => [
                    'clinical_safety' => 'Sensitive topic requires careful framing',
                    'age_appropriateness' => 'Content should be tiered by age group',
                ],
                'priority' => 'urgent',
            ],
            [
                'title' => 'Growth Mindset Workshop',
                'description' => 'Transform how you think about challenges and mistakes. Learn to embrace growth over perfection and develop resilience.',
                'status' => ContentModerationResult::STATUS_APPROVED_OVERRIDE,
                'score' => 0.82,
                'flags' => ['minor_wording' => 'Addressed after human review'],
                'priority' => 'normal',
                'human_reviewed' => true,
            ],
            [
                'title' => 'Conflict Resolution Skills',
                'description' => 'Learn to handle disagreements constructively, whether with peers, teachers, or family members. Practice de-escalation and communication.',
                'status' => ContentModerationResult::STATUS_PENDING,
                'score' => 0.70,
                'flags' => ['content_review' => 'Awaiting initial moderation'],
                'priority' => 'high',
            ],
            [
                'title' => 'Sleep Hygiene for Teens',
                'description' => 'Why sleep matters and how to get better rest. Covers sleep science, bedtime routines, and managing technology before bed.',
                'status' => ContentModerationResult::STATUS_PASSED,
                'score' => 0.91,
                'flags' => [],
                'priority' => 'low',
            ],
            [
                'title' => 'Dealing with Peer Pressure',
                'description' => 'Develop the confidence to make your own choices when facing pressure from friends or classmates. Learn assertiveness without aggression.',
                'status' => ContentModerationResult::STATUS_FLAGGED,
                'score' => 0.73,
                'flags' => ['accuracy' => 'Some scenarios could be more realistic'],
                'priority' => 'normal',
            ],
        ];

        foreach ($demoCourses as $index => $courseData) {
            // Create the MiniCourse
            $course = MiniCourse::create([
                'org_id' => $organization->id,
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'objectives' => [
                    'Understand key concepts',
                    'Practice practical techniques',
                    'Develop personal action plan',
                ],
                'rationale' => 'AI-generated course based on learner needs assessment data.',
                'expected_experience' => 'Interactive learning with reflections and practice exercises.',
                'course_type' => MiniCourse::TYPE_WELLNESS,
                'creation_source' => MiniCourse::SOURCE_AI_GENERATED,
                'ai_generation_context' => [
                    'trigger' => 'assessment_signals',
                    'generated_at' => now()->subDays(rand(1, 14))->toISOString(),
                ],
                'target_grades' => ['9', '10', '11', '12'],
                'target_risk_levels' => ['low', 'high'],
                'estimated_duration_minutes' => rand(20, 45),
                'status' => MiniCourse::STATUS_DRAFT,
                'created_by' => $admin->id,
            ]);

            // Create the moderation result
            $result = ContentModerationResult::create([
                'org_id' => $organization->id,
                'moderatable_type' => MiniCourse::class,
                'moderatable_id' => $course->id,
                'status' => $courseData['status'],
                'overall_score' => $courseData['score'],
                'age_appropriateness_score' => $courseData['score'] + (rand(-10, 10) / 100),
                'clinical_safety_score' => $courseData['score'] + (rand(-15, 5) / 100),
                'cultural_sensitivity_score' => $courseData['score'] + (rand(-5, 10) / 100),
                'accuracy_score' => $courseData['score'] + (rand(-10, 10) / 100),
                'flags' => $courseData['flags'],
                'recommendations' => ! empty($courseData['flags']) ? ['Review flagged items before approval'] : [],
                'human_reviewed' => $courseData['human_reviewed'] ?? false,
                'reviewed_by' => ($courseData['human_reviewed'] ?? false) ? $admin->id : null,
                'reviewed_at' => ($courseData['human_reviewed'] ?? false) ? now()->subDays(rand(1, 3)) : null,
                'model_version' => 'gpt-4-turbo-2024-04-09',
                'processing_time_ms' => rand(1500, 4500),
                'token_count' => rand(800, 2500),
                'assignment_priority' => $courseData['priority'],
                'created_at' => now()->subDays(rand(1, 10)),
            ]);

            // Assign some items to moderators
            if ($moderators->isNotEmpty() && $index % 3 === 0) {
                $moderator = $moderators->random();
                $result->update([
                    'assigned_to' => $moderator->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => now()->subDays(rand(1, 5)),
                    'due_at' => $courseData['priority'] === 'urgent'
                        ? now()->addDays(1)
                        : now()->addDays(rand(3, 7)),
                ]);
            }

            // Add some collaborators
            if ($moderators->count() > 1 && $index % 4 === 0) {
                $collaborator = $moderators->where('id', '!=', $result->assigned_to)->first();
                if ($collaborator) {
                    $result->update([
                        'collaborator_ids' => [$collaborator->id],
                    ]);
                }
            }
        }

        $this->command->info('Created '.count($demoCourses).' demo moderation items.');
        $this->command->info('Visit /admin/moderation to review the queue.');
    }
}
