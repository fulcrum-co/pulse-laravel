<?php

namespace Database\Seeders;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicHubDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a demo organization
        $org = Organization::where('org_name', 'like', '%Demo%')
            ->orWhere('org_name', 'like', '%Lincoln%')
            ->first();

        if (! $org) {
            $org = Organization::create([
                'org_type' => 'district',
                'org_name' => 'Demo School District',
                'primary_contact_email' => 'demo@example.com',
                'timezone' => 'America/Los_Angeles',
                'subscription_plan' => 'enterprise',
                'subscription_status' => 'active',
                'active' => true,
            ]);
        }

        $admin = User::where('org_id', $org->id)->first() ?? User::first();

        $this->command->info("Seeding public hub data for org: {$org->org_name} (ID: {$org->id})");
        $this->command->info("Access at: /hub/{$org->id}");

        // Create public resources
        $this->createPublicResources($org, $admin);

        // Create public courses
        $this->createPublicCourses($org, $admin);

        $this->command->info('Public hub demo data created successfully!');
    }

    private function createPublicResources(Organization $org, ?User $admin): void
    {
        $resources = [
            [
                'title' => 'Understanding Anxiety: A Student Guide',
                'description' => 'Learn what anxiety is, how it affects your body and mind, and discover practical strategies to manage anxious feelings. This comprehensive guide is designed specifically for students dealing with academic and social pressure.',
                'resource_type' => 'article',
                'category' => 'mental-health',
                'tags' => ['anxiety', 'stress', 'mental health', 'students'],
                'estimated_duration_minutes' => 15,
            ],
            [
                'title' => 'Mindfulness Meditation for Beginners',
                'description' => 'A gentle introduction to mindfulness meditation with guided exercises you can practice anywhere. Perfect for busy students looking to find calm in their daily routine.',
                'resource_type' => 'video',
                'category' => 'wellness',
                'tags' => ['mindfulness', 'meditation', 'relaxation', 'self-care'],
                'estimated_duration_minutes' => 12,
            ],
            [
                'title' => 'Study Skills That Actually Work',
                'description' => 'Discover evidence-based study techniques including spaced repetition, active recall, and the Pomodoro technique. Stop cramming and start learning effectively.',
                'resource_type' => 'article',
                'category' => 'academic',
                'tags' => ['study skills', 'learning', 'academics', 'productivity'],
                'estimated_duration_minutes' => 20,
            ],
            [
                'title' => 'Building Healthy Relationships',
                'description' => 'Learn about communication skills, setting boundaries, and recognizing healthy vs. unhealthy relationship patterns. Essential skills for navigating friendships and more.',
                'resource_type' => 'worksheet',
                'category' => 'social',
                'tags' => ['relationships', 'communication', 'boundaries', 'social skills'],
                'estimated_duration_minutes' => 25,
            ],
            [
                'title' => 'Sleep Better Tonight',
                'description' => 'Practical tips for improving your sleep quality, including establishing a bedtime routine, managing screen time, and creating an optimal sleep environment.',
                'resource_type' => 'article',
                'category' => 'wellness',
                'tags' => ['sleep', 'health', 'wellness', 'self-care'],
                'estimated_duration_minutes' => 10,
            ],
            [
                'title' => 'Dealing with Peer Pressure',
                'description' => 'Interactive guide to recognizing and responding to peer pressure. Learn to make decisions that align with your values while maintaining friendships.',
                'resource_type' => 'activity',
                'category' => 'social',
                'tags' => ['peer pressure', 'decision making', 'values', 'teens'],
                'estimated_duration_minutes' => 18,
            ],
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'Specific strategies for dealing with test anxiety, including preparation techniques, relaxation exercises, and mindset shifts that can help you perform your best.',
                'resource_type' => 'video',
                'category' => 'academic',
                'tags' => ['test anxiety', 'exams', 'stress', 'academics'],
                'estimated_duration_minutes' => 14,
            ],
            [
                'title' => 'Digital Wellness Guide',
                'description' => 'Learn to have a healthy relationship with technology. Topics include managing screen time, social media mindfulness, and recognizing digital burnout.',
                'resource_type' => 'article',
                'category' => 'wellness',
                'tags' => ['digital wellness', 'screen time', 'social media', 'technology'],
                'estimated_duration_minutes' => 12,
            ],
        ];

        foreach ($resources as $data) {
            Resource::updateOrCreate(
                ['org_id' => $org->id, 'title' => $data['title']],
                [
                    'description' => $data['description'],
                    'resource_type' => $data['resource_type'],
                    'category' => $data['category'],
                    'tags' => $data['tags'],
                    'estimated_duration_minutes' => $data['estimated_duration_minutes'],
                    'is_public' => true,
                    'active' => true,
                    'created_by' => $admin?->id,
                ]
            );
        }

        $this->command->info('Created ' . count($resources) . ' public resources');
    }

    private function createPublicCourses(Organization $org, ?User $admin): void
    {
        // Course 1: Stress Management
        $stressCourse = MiniCourse::updateOrCreate(
            ['org_id' => $org->id, 'title' => 'Stress Management Essentials'],
            [
                'slug' => 'stress-management-essentials-' . Str::random(6),
                'description' => 'A comprehensive course on understanding and managing stress. Learn practical techniques you can use immediately to feel calmer and more in control.',
                'objectives' => [
                    'Understand the science of stress and how it affects you',
                    'Learn 5 proven stress reduction techniques',
                    'Create a personalized stress management plan',
                    'Build lasting habits for long-term wellbeing',
                ],
                'rationale' => 'Stress management is a foundational life skill that impacts every area of your life - from academics to relationships to physical health.',
                'expected_experience' => 'Through videos, reflections, and practical exercises, you\'ll develop your own toolkit for managing stress effectively.',
                'course_type' => MiniCourse::TYPE_WELLNESS,
                'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
                'target_grades' => ['9', '10', '11', '12'],
                'estimated_duration_minutes' => 45,
                'status' => MiniCourse::STATUS_ACTIVE,
                'visibility' => MiniCourse::VISIBILITY_PUBLIC,
                'is_public' => true,
                'created_by' => $admin?->id,
                'published_at' => now()->subDays(30),
            ]
        );

        $this->createStressManagementSteps($stressCourse);
        $this->command->info('Created course: ' . $stressCourse->title);

        // Course 2: Study Skills
        $studyCourse = MiniCourse::updateOrCreate(
            ['org_id' => $org->id, 'title' => 'Study Smarter, Not Harder'],
            [
                'slug' => 'study-smarter-' . Str::random(6),
                'description' => 'Discover evidence-based study techniques that actually work. Learn how your brain learns best and transform your academic performance.',
                'objectives' => [
                    'Understand how memory and learning work',
                    'Master active recall and spaced repetition',
                    'Create an effective study schedule',
                    'Reduce test anxiety through better preparation',
                ],
                'rationale' => 'Most students were never taught how to study effectively. This course teaches the science-backed techniques that top performers use.',
                'expected_experience' => 'You\'ll learn about the science of learning and immediately apply it to your own study habits with practical exercises.',
                'course_type' => MiniCourse::TYPE_ACADEMIC,
                'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
                'target_grades' => ['9', '10', '11', '12'],
                'estimated_duration_minutes' => 35,
                'status' => MiniCourse::STATUS_ACTIVE,
                'visibility' => MiniCourse::VISIBILITY_PUBLIC,
                'is_public' => true,
                'created_by' => $admin?->id,
                'published_at' => now()->subDays(25),
            ]
        );

        $this->createStudySkillsSteps($studyCourse);
        $this->command->info('Created course: ' . $studyCourse->title);

        // Course 3: Building Confidence
        $confidenceCourse = MiniCourse::updateOrCreate(
            ['org_id' => $org->id, 'title' => 'Building Self-Confidence'],
            [
                'slug' => 'building-confidence-' . Str::random(6),
                'description' => 'Develop authentic self-confidence through practical exercises and mindset shifts. Learn to believe in yourself and take on new challenges.',
                'objectives' => [
                    'Understand what true confidence is (and isn\'t)',
                    'Identify and challenge negative self-talk',
                    'Practice confidence-building exercises',
                    'Develop a growth mindset',
                ],
                'rationale' => 'Self-confidence affects everything from academic performance to relationships. It\'s a skill that can be developed with practice.',
                'expected_experience' => 'Through reflection, practical exercises, and small challenges, you\'ll build genuine confidence from the inside out.',
                'course_type' => MiniCourse::TYPE_SKILL_BUILDING,
                'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
                'target_grades' => ['9', '10', '11', '12'],
                'estimated_duration_minutes' => 40,
                'status' => MiniCourse::STATUS_ACTIVE,
                'visibility' => MiniCourse::VISIBILITY_PUBLIC,
                'is_public' => true,
                'created_by' => $admin?->id,
                'published_at' => now()->subDays(20),
            ]
        );

        $this->createConfidenceSteps($confidenceCourse);
        $this->command->info('Created course: ' . $confidenceCourse->title);

        // Course 4: Goal Setting
        $goalCourse = MiniCourse::updateOrCreate(
            ['org_id' => $org->id, 'title' => 'Goal Setting for Success'],
            [
                'slug' => 'goal-setting-success-' . Str::random(6),
                'description' => 'Transform your dreams into achievable goals using the SMART framework. Learn to set, track, and achieve meaningful goals in any area of life.',
                'objectives' => [
                    'Master the SMART goal framework',
                    'Identify what truly matters to you',
                    'Break big goals into actionable steps',
                    'Build habits that support your goals',
                ],
                'rationale' => 'People who set clear goals are 10x more likely to achieve them. This course teaches you a proven system for turning aspirations into reality.',
                'expected_experience' => 'You\'ll walk away with at least one clear, actionable goal and a concrete plan to achieve it.',
                'course_type' => MiniCourse::TYPE_SKILL_BUILDING,
                'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
                'target_grades' => ['9', '10', '11', '12'],
                'estimated_duration_minutes' => 30,
                'status' => MiniCourse::STATUS_ACTIVE,
                'visibility' => MiniCourse::VISIBILITY_PUBLIC,
                'is_public' => true,
                'created_by' => $admin?->id,
                'published_at' => now()->subDays(15),
            ]
        );

        $this->createGoalSettingSteps($goalCourse);
        $this->command->info('Created course: ' . $goalCourse->title);
    }

    private function createStressManagementSteps(MiniCourse $course): void
    {
        // Delete existing steps to avoid duplicates
        $course->steps()->delete();

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 0,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'What is Stress?',
            'description' => 'Understanding the science of stress and how it affects your body and mind.',
            'instructions' => 'Read through this introduction to understand stress better.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Understanding Stress\n\nStress is your body's natural response to challenges. While some stress can be motivating, too much can affect your health and wellbeing.\n\n## Physical Signs\n- Headaches\n- Fatigue\n- Trouble sleeping\n- Muscle tension\n\n## Emotional Signs\n- Feeling overwhelmed\n- Irritability\n- Difficulty concentrating\n- Anxiety",
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'Your Stress Signals',
            'description' => 'Reflect on how stress shows up in your life.',
            'instructions' => 'Think about recent stressful situations and answer honestly.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'What physical signs do you notice when stressed?',
                    'What situations trigger your stress most often?',
                    'How do you currently cope with stress?',
                ],
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_PRACTICE,
            'title' => 'Box Breathing Exercise',
            'description' => 'Practice a powerful breathing technique used by Navy SEALs.',
            'instructions' => 'Follow the guided breathing exercise to calm your nervous system.',
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'body' => "Box breathing is a simple but powerful technique to activate your parasympathetic nervous system and reduce stress.\n\n**How it works:**\n- Breathe in for 4 seconds\n- Hold for 4 seconds\n- Breathe out for 4 seconds\n- Hold for 4 seconds\n\nRepeat 4 times.",
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Create Your Stress Plan',
            'description' => 'Build a personalized stress management toolkit.',
            'instructions' => 'Based on what you\'ve learned, create your personal plan.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'List 3 healthy coping strategies you will try this week',
                    'Who can you talk to when feeling stressed?',
                    'What will you do if your usual strategies aren\'t working?',
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);
    }

    private function createStudySkillsSteps(MiniCourse $course): void
    {
        $course->steps()->delete();

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 0,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'How Your Brain Learns',
            'description' => 'Understanding the science of memory and learning.',
            'instructions' => 'Read about how your brain processes information.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# The Science of Learning\n\nYour brain doesn't work like a computer - understanding how memory works can help you study smarter.\n\n## Key Concepts\n- **Encoding**: How information gets into your brain\n- **Storage**: How it's kept\n- **Retrieval**: How you access it later\n\n## The Forgetting Curve\nWithout review, you forget 70% of new information within 24 hours!",
            ],
            'estimated_duration_minutes' => 6,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Active Recall & Spaced Repetition',
            'description' => 'The two most powerful study techniques.',
            'instructions' => 'Learn why testing yourself beats re-reading.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Study Techniques That Work\n\n## Active Recall\nTest yourself instead of just re-reading. The effort of trying to remember strengthens memory.\n\n## Spaced Repetition\nSpread study sessions over time instead of cramming.\n\n**Optimal schedule:**\n- Day 1: Learn\n- Day 2: First review\n- Day 4: Second review\n- Day 7: Third review\n- Day 14: Fourth review",
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Create Your Study Schedule',
            'description' => 'Plan your study sessions for the week.',
            'instructions' => 'Apply what you\'ve learned to create a realistic plan.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'Which subject will you focus on first?',
                    'When will you have your study sessions? (specific days/times)',
                    'What active recall techniques will you use?',
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);
    }

    private function createConfidenceSteps(MiniCourse $course): void
    {
        $course->steps()->delete();

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 0,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'What is True Confidence?',
            'description' => 'Understanding the difference between confidence and arrogance.',
            'instructions' => 'Learn what self-confidence really means.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Understanding Self-Confidence\n\nTrue confidence is believing in your ability to handle challenges and learn from mistakes.\n\n## Confidence is NOT:\n- Being perfect\n- Never feeling afraid\n- Thinking you're better than others\n\n## Confidence IS:\n- Believing you can figure things out\n- Taking action despite fear\n- Being kind to yourself when you make mistakes",
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'Your Confidence Inventory',
            'description' => 'Assess where you feel confident and where you struggle.',
            'instructions' => 'Answer honestly - this is just for you.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'On a scale of 1-10, how confident do you generally feel?',
                    'In what situations do you feel most confident?',
                    'When do you feel least confident?',
                    'What negative thoughts do you have about yourself?',
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Challenging Negative Self-Talk',
            'description' => 'Learn to recognize and reframe negative thoughts.',
            'instructions' => 'Discover techniques for changing your inner dialogue.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Reframing Negative Self-Talk\n\n## Common Patterns\n- **All-or-nothing**: \"I failed, I'm stupid\"\n- **Mind reading**: \"Everyone thinks I'm weird\"\n- **Catastrophizing**: \"If I fail, my life is over\"\n\n## Reframing Examples\n| Negative | Reframe |\n|----------|----------|\n| \"I'm stupid\" | \"I'm still learning\" |\n| \"I'll fail\" | \"I don't know yet - let me try\" |\n| \"I can't do anything\" | \"I'm good at some things\" |",
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Your Confidence Challenge',
            'description' => 'Take a small step outside your comfort zone.',
            'instructions' => 'Commit to one confidence-building action this week.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'What small challenge will you take on this week?',
                    'What negative thought might try to stop you? How will you reframe it?',
                    'Who can support you in this challenge?',
                ],
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);
    }

    private function createGoalSettingSteps(MiniCourse $course): void
    {
        $course->steps()->delete();

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 0,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'The SMART Framework',
            'description' => 'Learn how to set goals that actually work.',
            'instructions' => 'Discover the proven SMART goal framework.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# SMART Goals\n\n**S** - Specific: Clear and well-defined\n**M** - Measurable: You can track progress\n**A** - Achievable: Challenging but realistic\n**R** - Relevant: Matters to YOU\n**T** - Time-bound: Has a deadline\n\n## Example\n❌ \"Get better grades\"\n✅ \"Raise my math grade from B to A by semester end by studying 30 min daily\"",
            ],
            'estimated_duration_minutes' => 6,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'What Matters to You?',
            'description' => 'Identify areas where you want to grow.',
            'instructions' => 'Reflect on different areas of your life.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'What do you want your life to look like in 1 year?',
                    'What skill would make the biggest difference if you developed it?',
                    'What achievement would make you genuinely proud?',
                ],
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Write Your SMART Goal',
            'description' => 'Create your first SMART goal.',
            'instructions' => 'Use the framework to write a complete, actionable goal.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'Specific - What exactly do you want to achieve?',
                    'Measurable - How will you know you\'ve succeeded?',
                    'Achievable - What steps will you take? List 3-5 actions.',
                    'Relevant - Why does this matter to you?',
                    'Time-bound - By when will you achieve this?',
                ],
            ],
            'estimated_duration_minutes' => 12,
            'is_required' => true,
        ]);
    }
}
