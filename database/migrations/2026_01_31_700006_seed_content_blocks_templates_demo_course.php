<?php

use App\Models\ContentBlock;
use App\Models\ContentTag;
use App\Models\CourseTemplate;
use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Get the demo school organization
        $demoSchool = Organization::where('org_name', 'Lincoln High School')->first();

        if (! $demoSchool) {
            return;
        }

        // Create system-level content tags
        $tags = $this->createSystemTags();

        // Create demo content blocks
        $this->createDemoContentBlocks($demoSchool->id, $tags);

        // Create system course templates
        $this->createSystemTemplates();

        // Create the rich demo course: "Building Emotional Resilience"
        $this->createRichDemoCourse($demoSchool->id);

        // Set default course generation settings for the demo school
        $demoSchool->update([
            'course_generation_settings' => [
                'enabled' => true,
                'approval_required' => true,
                'approval_roles' => ['admin', 'counselor'],
                'auto_approve_templates' => false,
                'allowed_triggers' => ['risk_threshold', 'workflow', 'manual'],
                'risk_threshold_config' => [
                    'enabled' => true,
                    'min_risk_score' => 0.7,
                    'risk_factors' => ['attendance', 'behavior', 'academic'],
                    'cooldown_days' => 30,
                ],
                'default_generation_strategy' => 'hybrid',
                'external_sources' => [
                    'youtube_enabled' => true,
                    'khan_academy_enabled' => true,
                    'custom_uploads_enabled' => true,
                ],
                'ai_config' => [
                    'model' => 'claude-sonnet',
                    'creativity_level' => 'balanced',
                    'max_steps_per_course' => 10,
                ],
            ],
        ]);
    }

    protected function createSystemTags(): array
    {
        $tags = [];

        // Topics
        $topicData = [
            ['name' => 'Anxiety', 'slug' => 'anxiety', 'color' => '#EF4444'],
            ['name' => 'Stress Management', 'slug' => 'stress-management', 'color' => '#F59E0B'],
            ['name' => 'Resilience', 'slug' => 'resilience', 'color' => '#10B981'],
            ['name' => 'Mindfulness', 'slug' => 'mindfulness', 'color' => '#6366F1'],
            ['name' => 'Self-Care', 'slug' => 'self-care', 'color' => '#EC4899'],
            ['name' => 'Communication', 'slug' => 'communication', 'color' => '#8B5CF6'],
            ['name' => 'Study Skills', 'slug' => 'study-skills', 'color' => '#3B82F6'],
            ['name' => 'Time Management', 'slug' => 'time-management', 'color' => '#14B8A6'],
        ];

        foreach ($topicData as $topic) {
            $tags[$topic['slug']] = ContentTag::create([
                'org_id' => null, // System-level
                'name' => $topic['name'],
                'slug' => $topic['slug'],
                'category' => ContentTag::CATEGORY_TOPIC,
                'color' => $topic['color'],
            ]);
        }

        // Skills
        $skillData = [
            ['name' => 'Breathing Exercises', 'slug' => 'breathing', 'color' => '#22D3EE'],
            ['name' => 'Journaling', 'slug' => 'journaling', 'color' => '#A78BFA'],
            ['name' => 'Goal Setting', 'slug' => 'goal-setting', 'color' => '#FB923C'],
            ['name' => 'Emotional Awareness', 'slug' => 'emotional-awareness', 'color' => '#F472B6'],
        ];

        foreach ($skillData as $skill) {
            $tags[$skill['slug']] = ContentTag::create([
                'org_id' => null,
                'name' => $skill['name'],
                'slug' => $skill['slug'],
                'category' => ContentTag::CATEGORY_SKILL,
                'color' => $skill['color'],
            ]);
        }

        // Risk factors
        $riskData = [
            ['name' => 'Attendance Risk', 'slug' => 'attendance-risk', 'color' => '#DC2626'],
            ['name' => 'Academic Risk', 'slug' => 'academic-risk', 'color' => '#EA580C'],
            ['name' => 'Behavioral Risk', 'slug' => 'behavioral-risk', 'color' => '#CA8A04'],
        ];

        foreach ($riskData as $risk) {
            $tags[$risk['slug']] = ContentTag::create([
                'org_id' => null,
                'name' => $risk['name'],
                'slug' => $risk['slug'],
                'category' => ContentTag::CATEGORY_RISK_FACTOR,
                'color' => $risk['color'],
            ]);
        }

        return $tags;
    }

    protected function createDemoContentBlocks(int $orgId, array $tags): void
    {
        // Video: Introduction to Resilience
        $video1 = ContentBlock::create([
            'org_id' => null, // System content
            'title' => 'What is Emotional Resilience?',
            'slug' => 'intro-emotional-resilience',
            'description' => 'A 3-minute animated explainer on what emotional resilience means and why it matters.',
            'block_type' => ContentBlock::TYPE_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Placeholder
                'duration_seconds' => 180,
                'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
            ],
            'source_type' => ContentBlock::SOURCE_YOUTUBE,
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'topics' => ['resilience', 'stress-management'],
            'skills' => ['emotional-awareness'],
            'grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'target_risk_factors' => ['academic-risk', 'behavioral-risk'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);

        // Video: Deep Breathing Exercise
        ContentBlock::create([
            'org_id' => null,
            'title' => '4-7-8 Breathing Technique',
            'slug' => 'breathing-4-7-8',
            'description' => 'Guided breathing exercise to calm anxiety and stress.',
            'block_type' => ContentBlock::TYPE_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/odADwWzHR24',
                'duration_seconds' => 240,
            ],
            'source_type' => ContentBlock::SOURCE_YOUTUBE,
            'topics' => ['anxiety', 'stress-management', 'mindfulness'],
            'skills' => ['breathing'],
            'grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);

        // Text: Grounding Technique
        ContentBlock::create([
            'org_id' => null,
            'title' => '5-4-3-2-1 Grounding Technique',
            'slug' => 'grounding-54321',
            'description' => 'A simple grounding exercise for managing anxiety in the moment.',
            'block_type' => ContentBlock::TYPE_TEXT,
            'content_data' => [
                'body' => "## The 5-4-3-2-1 Grounding Technique\n\nWhen you're feeling overwhelmed or anxious, this technique helps bring you back to the present moment.\n\n### How to Practice:\n\n**5 - SEE**: Look around and name 5 things you can see\n- What colors are they?\n- What shapes?\n\n**4 - TOUCH**: Notice 4 things you can physically feel\n- The chair beneath you\n- Your feet on the floor\n\n**3 - HEAR**: Listen for 3 sounds around you\n- Near and far\n- Soft and loud\n\n**2 - SMELL**: Identify 2 scents you can smell\n- Or remember favorite smells\n\n**1 - TASTE**: Notice 1 thing you can taste\n- Or take a sip of water\n\n### Tips for Success:\n- Practice when calm first\n- Use it anywhere, anytime\n- There's no wrong way to do it",
                'key_points' => [
                    'Uses all 5 senses to ground you in the present',
                    'Can be done anywhere without anyone noticing',
                    'Gets easier with practice',
                ],
            ],
            'source_type' => ContentBlock::SOURCE_INTERNAL,
            'topics' => ['anxiety', 'mindfulness'],
            'skills' => ['emotional-awareness'],
            'grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);

        // Activity: Stress Journal
        ContentBlock::create([
            'org_id' => null,
            'title' => 'Stress Trigger Journal',
            'slug' => 'stress-trigger-journal',
            'description' => 'Interactive journaling activity to identify personal stress patterns.',
            'block_type' => ContentBlock::TYPE_ACTIVITY,
            'content_data' => [
                'type' => 'journaling',
                'instructions' => 'Reflect on a recent stressful situation and explore your patterns.',
                'prompts' => [
                    'Describe a recent situation that caused you stress.',
                    'What physical sensations did you notice in your body?',
                    'What thoughts were going through your mind?',
                    'Looking back, what might have helped in that moment?',
                ],
                'estimated_time_minutes' => 10,
            ],
            'source_type' => ContentBlock::SOURCE_INTERNAL,
            'topics' => ['stress-management', 'self-care'],
            'skills' => ['journaling', 'emotional-awareness'],
            'grade_levels' => ['7', '8', '9', '10', '11', '12'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);

        // Assessment: Resilience Self-Check
        ContentBlock::create([
            'org_id' => null,
            'title' => 'Resilience Self-Assessment',
            'slug' => 'resilience-self-assessment',
            'description' => 'Quick self-assessment to understand your current resilience strengths.',
            'block_type' => ContentBlock::TYPE_ASSESSMENT,
            'content_data' => [
                'instructions' => 'Answer honestly - there are no right or wrong answers. This is just to help you understand yourself better.',
                'questions' => [
                    [
                        'id' => 1,
                        'question' => 'When facing a difficult challenge, I typically...',
                        'type' => 'multiple_choice',
                        'options' => [
                            'Feel overwhelmed and want to give up',
                            'Take time to think before acting',
                            'Ask someone for help right away',
                            'Try multiple approaches until one works',
                        ],
                    ],
                    [
                        'id' => 2,
                        'question' => 'After experiencing a setback, I usually recover...',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Very slowly',
                        'max_label' => 'Very quickly',
                    ],
                    [
                        'id' => 3,
                        'question' => 'I have people I can talk to when things are tough.',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Strongly disagree',
                        'max_label' => 'Strongly agree',
                    ],
                    [
                        'id' => 4,
                        'question' => 'When I make a mistake, my first reaction is usually...',
                        'type' => 'multiple_choice',
                        'options' => [
                            'To be very hard on myself',
                            'To blame others or circumstances',
                            'To learn from it and move on',
                            'To pretend it didn\'t happen',
                        ],
                    ],
                    [
                        'id' => 5,
                        'question' => 'I believe I can get through difficult times.',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Strongly disagree',
                        'max_label' => 'Strongly agree',
                    ],
                ],
            ],
            'source_type' => ContentBlock::SOURCE_INTERNAL,
            'topics' => ['resilience'],
            'skills' => ['emotional-awareness'],
            'grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);

        // Document: Resilience Plan Worksheet
        ContentBlock::create([
            'org_id' => null,
            'title' => 'My Resilience Plan Worksheet',
            'slug' => 'resilience-plan-worksheet',
            'description' => 'Downloadable worksheet for creating a personal resilience action plan.',
            'block_type' => ContentBlock::TYPE_DOCUMENT,
            'content_data' => [
                'filename' => 'resilience-plan-worksheet.pdf',
                'file_url' => '/documents/resilience-plan-worksheet.pdf', // Placeholder
                'file_size' => '145 KB',
                'file_type' => 'pdf',
                'preview_text' => 'Create your personal plan for building resilience including coping strategies, support network, and self-care activities.',
            ],
            'source_type' => ContentBlock::SOURCE_INTERNAL,
            'topics' => ['resilience', 'self-care'],
            'skills' => ['goal-setting'],
            'grade_levels' => ['7', '8', '9', '10', '11', '12'],
            'status' => ContentBlock::STATUS_ACTIVE,
        ]);
    }

    protected function createSystemTemplates(): void
    {
        // Wellness/SEL Course Template
        CourseTemplate::create([
            'org_id' => null,
            'name' => 'Wellness Intervention Course',
            'slug' => 'wellness-intervention',
            'description' => 'A comprehensive template for wellness and social-emotional learning courses with video, activities, reflection, and action planning.',
            'course_type' => 'wellness',
            'template_data' => [
                'objectives_template' => [
                    'Understand key concepts related to {topic}',
                    'Learn and practice {skill_count} practical strategies',
                    'Develop a personal action plan',
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title_template' => 'Welcome & Introduction to {topic}',
                        'step_type' => 'content',
                        'content_type' => 'video',
                        'estimated_duration' => 5,
                        'content_block_query' => [
                            'block_type' => 'video',
                            'topics' => ['{primary_topic}'],
                        ],
                        'fallback_ai_prompt' => 'Create a welcoming introduction to {topic} for {grade_level} students. Explain why this topic matters and what they will learn.',
                    ],
                    [
                        'order' => 2,
                        'title_template' => 'Understanding {topic}',
                        'step_type' => 'content',
                        'content_type' => 'text',
                        'estimated_duration' => 8,
                        'content_block_query' => [
                            'block_type' => 'text',
                            'topics' => ['{primary_topic}'],
                        ],
                        'fallback_ai_prompt' => 'Create educational content explaining {topic} in depth for {grade_level} students.',
                    ],
                    [
                        'order' => 3,
                        'title_template' => 'Self-Assessment',
                        'step_type' => 'assessment',
                        'estimated_duration' => 5,
                        'content_block_query' => [
                            'block_type' => 'assessment',
                            'topics' => ['{primary_topic}'],
                        ],
                        'fallback_ai_prompt' => 'Create a 5-question self-assessment for students to reflect on their current experience with {topic}.',
                    ],
                    [
                        'order' => 4,
                        'title_template' => 'Strategy: {strategy_1}',
                        'step_type' => 'practice',
                        'estimated_duration' => 7,
                        'content_block_query' => [
                            'block_type' => 'activity',
                            'skills' => ['{primary_skill}'],
                        ],
                        'fallback_ai_prompt' => 'Create a practical exercise teaching {strategy_1} for managing {topic}.',
                    ],
                    [
                        'order' => 5,
                        'title_template' => 'Reflection & Journaling',
                        'step_type' => 'reflection',
                        'estimated_duration' => 5,
                        'fallback_ai_prompt' => 'Create 3 reflection prompts for students to process what they learned about {topic}.',
                    ],
                    [
                        'order' => 6,
                        'title_template' => 'Create Your Action Plan',
                        'step_type' => 'action',
                        'estimated_duration' => 6,
                        'content_block_query' => [
                            'block_type' => 'document',
                            'topics' => ['{primary_topic}'],
                        ],
                        'fallback_ai_prompt' => 'Guide students to create a personal action plan for applying {topic} strategies.',
                    ],
                    [
                        'order' => 7,
                        'title_template' => 'Connect with Support',
                        'step_type' => 'human_connection',
                        'estimated_duration' => 3,
                        'fallback_ai_prompt' => 'Provide information about when and how to seek additional support for {topic}.',
                    ],
                    [
                        'order' => 8,
                        'title_template' => 'Course Summary & Next Steps',
                        'step_type' => 'checkpoint',
                        'estimated_duration' => 2,
                        'fallback_ai_prompt' => 'Summarize key learnings and suggest next steps for continued growth in {topic}.',
                    ],
                ],
                'variables' => [
                    'topic' => ['type' => 'string', 'required' => true],
                    'primary_topic' => ['type' => 'string', 'required' => true],
                    'primary_skill' => ['type' => 'string', 'required' => false],
                    'strategy_1' => ['type' => 'string', 'required' => false],
                    'skill_count' => ['type' => 'number', 'required' => false, 'default' => 3],
                    'grade_level' => ['type' => 'string', 'required' => true],
                ],
            ],
            'target_risk_factors' => ['academic-risk', 'behavioral-risk', 'attendance-risk'],
            'target_grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'estimated_duration_minutes' => 45,
            'is_system' => true,
            'status' => CourseTemplate::STATUS_ACTIVE,
        ]);

        // Study Skills Template
        CourseTemplate::create([
            'org_id' => null,
            'name' => 'Academic Skills Course',
            'slug' => 'academic-skills',
            'description' => 'Template for academic support courses covering study techniques, time management, and organization.',
            'course_type' => 'academic',
            'template_data' => [
                'objectives_template' => [
                    'Learn effective strategies for {topic}',
                    'Apply techniques to real academic situations',
                    'Develop sustainable habits for success',
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title_template' => 'Why {topic} Matters',
                        'step_type' => 'content',
                        'content_type' => 'video',
                        'estimated_duration' => 5,
                    ],
                    [
                        'order' => 2,
                        'title_template' => '{topic} Strategies',
                        'step_type' => 'content',
                        'content_type' => 'text',
                        'estimated_duration' => 10,
                    ],
                    [
                        'order' => 3,
                        'title_template' => 'Practice Activity',
                        'step_type' => 'practice',
                        'estimated_duration' => 10,
                    ],
                    [
                        'order' => 4,
                        'title_template' => 'Apply to Your Schoolwork',
                        'step_type' => 'action',
                        'estimated_duration' => 8,
                    ],
                    [
                        'order' => 5,
                        'title_template' => 'Summary & Commitment',
                        'step_type' => 'checkpoint',
                        'estimated_duration' => 3,
                    ],
                ],
                'variables' => [
                    'topic' => ['type' => 'string', 'required' => true],
                    'grade_level' => ['type' => 'string', 'required' => true],
                ],
            ],
            'target_risk_factors' => ['academic-risk'],
            'target_grade_levels' => ['6', '7', '8', '9', '10', '11', '12'],
            'estimated_duration_minutes' => 36,
            'is_system' => true,
            'status' => CourseTemplate::STATUS_ACTIVE,
        ]);
    }

    protected function createRichDemoCourse(int $orgId): void
    {
        // Create the comprehensive "Building Emotional Resilience" course
        $course = MiniCourse::create([
            'org_id' => $orgId,
            'title' => 'Building Emotional Resilience',
            'description' => 'Learn practical strategies to bounce back from challenges, manage stress, and build lasting mental strength.',
            'objectives' => [
                'Understand what emotional resilience means and why it matters',
                'Learn 3 practical coping strategies for difficult moments',
                'Practice grounding and breathing techniques',
                'Create a personal resilience action plan',
            ],
            'rationale' => 'This course was designed to help students develop essential life skills for managing stress and building mental strength. Research shows that resilience can be learned and strengthened with practice.',
            'expected_experience' => 'You\'ll watch short videos, try interactive exercises, reflect on your experiences, and create a personal plan. The course takes about 45 minutes but you can go at your own pace.',
            'course_type' => MiniCourse::TYPE_WELLNESS,
            'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
            'target_grades' => ['7', '8', '9', '10', '11', '12'],
            'target_risk_levels' => ['moderate', 'high'],
            'target_needs' => ['stress-management', 'anxiety', 'resilience'],
            'estimated_duration_minutes' => 45,
            'difficulty_level' => 'beginner',
            'status' => MiniCourse::STATUS_ACTIVE,
            'is_public' => false,
            'published_at' => now(),
        ]);

        // Step 1: Welcome & Introduction (video)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Welcome & Introduction',
            'description' => 'Get an overview of what you\'ll learn and why emotional resilience matters.',
            'step_type' => 'content',
            'content_type' => 'video',
            'sort_order' => 1,
            'estimated_duration_minutes' => 5,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/HfxMbF_sQyA',
                'body' => "## Welcome to Building Emotional Resilience\n\nIn this course, you'll learn practical strategies to help you:\n\n- **Bounce back** from setbacks and challenges\n- **Manage stress** when things get overwhelming\n- **Build confidence** in your ability to handle difficult situations\n\nLet's get started by understanding what resilience really means.",
                'key_points' => [
                    'Resilience is a skill that can be learned',
                    'Everyone faces challenges - it\'s how we respond that matters',
                    'Small daily practices build lasting strength',
                ],
            ],
        ]);

        // Step 2: Understanding Emotional Resilience (text with download)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Understanding Emotional Resilience',
            'description' => 'Learn what emotional resilience means and the key factors that help build it.',
            'step_type' => 'content',
            'content_type' => 'text',
            'sort_order' => 2,
            'estimated_duration_minutes' => 8,
            'content_data' => [
                'body' => "## What is Emotional Resilience?\n\nEmotional resilience is your ability to adapt and recover when things don't go as planned. It's not about avoiding stress or pretending everything is fine — it's about developing the skills to cope, grow, and even thrive in the face of challenges.\n\n### The Four Pillars of Resilience\n\n**1. Self-Awareness**\nUnderstanding your emotions, triggers, and patterns is the first step. When you know how you typically react to stress, you can choose how to respond instead.\n\n**2. Connection**\nHaving supportive relationships gives us strength. Whether it's friends, family, teachers, or counselors — knowing you're not alone makes a huge difference.\n\n**3. Coping Strategies**\nThese are your tools for managing difficult moments. Breathing exercises, grounding techniques, journaling, and physical activity are all examples.\n\n**4. Growth Mindset**\nBelieving that you can learn, grow, and improve — even from failures — is at the heart of resilience.\n\n### Why Does This Matter?\n\nResearch shows that students with higher resilience:\n- Perform better academically\n- Have better relationships\n- Experience less anxiety and depression\n- Are more likely to achieve their goals\n\nThe good news? **Resilience can be strengthened at any age.** The strategies you'll learn in this course will serve you for life.",
                'key_points' => [
                    'Resilience = the ability to adapt and recover from challenges',
                    'Four pillars: Self-awareness, Connection, Coping strategies, Growth mindset',
                    'Resilience leads to better academic and life outcomes',
                ],
                'downloads' => [
                    [
                        'title' => 'Resilience Quick Reference Guide',
                        'filename' => 'resilience-guide.pdf',
                        'type' => 'pdf',
                        'size' => '245 KB',
                    ],
                ],
            ],
        ]);

        // Step 3: Self-Assessment (quiz)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Self-Assessment: Your Current Resilience',
            'description' => 'Take a quick self-assessment to understand your current resilience strengths and areas for growth.',
            'step_type' => 'assessment',
            'content_type' => 'interactive',
            'sort_order' => 3,
            'estimated_duration_minutes' => 5,
            'content_data' => [
                'instructions' => 'Answer the following questions honestly. There are no right or wrong answers — this is simply to help you understand yourself better.',
                'questions' => [
                    [
                        'id' => 1,
                        'question' => 'When facing a challenge, I typically...',
                        'type' => 'multiple_choice',
                        'options' => [
                            'Feel overwhelmed and want to avoid it',
                            'Take time to think before acting',
                            'Jump right in without planning',
                            'Ask others for help immediately',
                        ],
                    ],
                    [
                        'id' => 2,
                        'question' => 'After a setback, I usually recover...',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Very slowly',
                        'max_label' => 'Very quickly',
                    ],
                    [
                        'id' => 3,
                        'question' => 'I have people I can talk to when things are tough.',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Strongly disagree',
                        'max_label' => 'Strongly agree',
                    ],
                    [
                        'id' => 4,
                        'question' => 'When I make a mistake, my first reaction is usually to...',
                        'type' => 'multiple_choice',
                        'options' => [
                            'Be very hard on myself',
                            'Blame others or circumstances',
                            'Learn from it and move on',
                            'Pretend it didn\'t happen',
                        ],
                    ],
                    [
                        'id' => 5,
                        'question' => 'I believe I can handle whatever comes my way.',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Strongly disagree',
                        'max_label' => 'Strongly agree',
                    ],
                ],
            ],
        ]);

        // Step 4: Coping Strategy #1 - Deep Breathing (practice/video)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Coping Strategy #1: Deep Breathing',
            'description' => 'Learn and practice the 4-7-8 breathing technique for immediate calm.',
            'step_type' => 'practice',
            'content_type' => 'video',
            'sort_order' => 4,
            'estimated_duration_minutes' => 7,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/odADwWzHR24',
                'body' => "## The 4-7-8 Breathing Technique\n\nThis powerful technique can help calm your nervous system in just a few minutes.\n\n### How to Practice:\n\n1. **Breathe IN** through your nose for **4 seconds**\n2. **HOLD** your breath for **7 seconds**\n3. **Breathe OUT** through your mouth for **8 seconds**\n\nRepeat this cycle 3-4 times.\n\n### When to Use It:\n- Before a test or presentation\n- When feeling anxious or overwhelmed\n- Before bed to help sleep\n- Any time you need to reset",
                'key_points' => [
                    'Breathe in for 4, hold for 7, out for 8',
                    'Repeat 3-4 times for best effect',
                    'Practice when calm so it\'s easy when stressed',
                ],
                'activity' => [
                    'type' => 'breathing_exercise',
                    'title' => 'Let\'s Practice Together',
                    'instructions' => 'Follow along with the timer below:',
                    'steps' => [
                        ['action' => 'Breathe In', 'duration' => 4],
                        ['action' => 'Hold', 'duration' => 7],
                        ['action' => 'Breathe Out', 'duration' => 8],
                    ],
                    'cycles' => 3,
                ],
                'downloads' => [
                    [
                        'title' => 'Breathing Exercise Card',
                        'filename' => 'breathing-card.pdf',
                        'type' => 'pdf',
                        'size' => '89 KB',
                    ],
                ],
            ],
        ]);

        // Step 5: Coping Strategy #2 - Grounding (practice/text)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Coping Strategy #2: Grounding Technique',
            'description' => 'Learn the 5-4-3-2-1 grounding technique to stay present during anxious moments.',
            'step_type' => 'practice',
            'content_type' => 'text',
            'sort_order' => 5,
            'estimated_duration_minutes' => 6,
            'content_data' => [
                'body' => "## The 5-4-3-2-1 Grounding Technique\n\nWhen anxiety pulls you into worried thoughts about the future or regrets about the past, grounding brings you back to **right now**.\n\n### The Steps:\n\n**5 THINGS you can SEE**\nLook around and name them. A clock, a window, your shoes...\n\n**4 THINGS you can TOUCH**\nFeel the texture of your shirt, the smooth desk, the floor beneath you...\n\n**3 THINGS you can HEAR**\nThe hum of lights, birds outside, footsteps in the hall...\n\n**2 THINGS you can SMELL**\nMaybe coffee, fresh air, or your shampoo...\n\n**1 THING you can TASTE**\nTake a sip of water or notice what's already there...\n\n### Why It Works\n\nAnxiety lives in your thoughts. By focusing on your senses, you shift attention away from worried thinking and into the present moment.\n\n### Practice Now\n\nTry it right now, wherever you are. Name:\n- 5 things you see\n- 4 things you feel\n- 3 things you hear\n- 2 things you smell\n- 1 thing you taste",
                'key_points' => [
                    'Use all 5 senses to ground yourself',
                    'Works anywhere without anyone noticing',
                    'Gets easier and more effective with practice',
                ],
            ],
        ]);

        // Step 6: Reflection - Stress Triggers (reflection)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Reflection: Your Stress Triggers',
            'description' => 'Take time to reflect on your personal stress patterns and responses.',
            'step_type' => 'reflection',
            'content_type' => 'text',
            'sort_order' => 6,
            'estimated_duration_minutes' => 5,
            'content_data' => [
                'body' => "## Time to Reflect\n\nUnderstanding your personal patterns is key to building resilience. Take a few minutes to think about these questions.",
                'prompts' => [
                    'Think of a recent stressful situation. What happened, and how did you respond?',
                    'What patterns do you notice in how you react to stress? (e.g., avoiding, worrying, getting angry)',
                    'Which of the techniques you learned today might help you next time?',
                ],
            ],
        ]);

        // Step 7: Creating Your Resilience Plan (action)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Creating Your Resilience Plan',
            'description' => 'Build your personal action plan for practicing resilience.',
            'step_type' => 'action',
            'content_type' => 'text',
            'sort_order' => 7,
            'estimated_duration_minutes' => 6,
            'content_data' => [
                'body' => "## Your Personal Resilience Plan\n\nNow it's time to create a plan you can actually use. Download the worksheet and complete each section.\n\n### Your Plan Should Include:\n\n**1. My Go-To Coping Strategy**\nWhich technique will you try first when stressed?\n\n**2. My Support Network**\nWho are 2-3 people you can reach out to?\n\n**3. My Warning Signs**\nWhat does stress feel like in your body? What behaviors do you notice?\n\n**4. My Commitment**\nOne small thing you'll do this week to practice resilience.\n\n### Remember\n\nResilience is built through **small, consistent actions** — not dramatic changes. Pick one thing and start there.",
                'downloads' => [
                    [
                        'title' => 'My Resilience Plan Worksheet',
                        'filename' => 'resilience-plan-worksheet.pdf',
                        'type' => 'pdf',
                        'size' => '145 KB',
                    ],
                ],
            ],
        ]);

        // Step 8: Connect with Support (human_connection)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Connect with Support',
            'description' => 'Learn when and how to reach out for additional support.',
            'step_type' => 'human_connection',
            'content_type' => 'text',
            'sort_order' => 8,
            'estimated_duration_minutes' => 3,
            'content_data' => [
                'body' => "## You Don't Have to Do This Alone\n\nBuilding resilience is important, but it's also important to know when to ask for help.\n\n### When to Reach Out:\n\n- You're feeling overwhelmed more days than not\n- The strategies in this course aren't enough\n- You're having thoughts of hurting yourself\n- You just want someone to talk to\n\n### Who Can Help:\n\n**At School:**\n- Your school counselor\n- A trusted teacher\n- The main office\n\n**Outside School:**\n- A parent or guardian\n- A coach or mentor\n- Crisis Text Line: Text HOME to 741741\n\n### Remember\n\nAsking for help is a **sign of strength**, not weakness. The most resilient people know they don't have to face everything alone.",
                'resources' => [
                    [
                        'title' => 'School Counseling Office',
                        'description' => 'Schedule a time to talk with your school counselor',
                        'action_type' => 'contact',
                    ],
                    [
                        'title' => 'Crisis Text Line',
                        'description' => 'Free, 24/7 support via text',
                        'action_type' => 'external_link',
                        'url' => 'https://www.crisistextline.org/',
                    ],
                ],
            ],
        ]);

        // Step 9: Course Completion & Next Steps (checkpoint)
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'title' => 'Congratulations!',
            'description' => 'Review what you learned and plan your next steps.',
            'step_type' => 'checkpoint',
            'content_type' => 'text',
            'sort_order' => 9,
            'estimated_duration_minutes' => 2,
            'content_data' => [
                'body' => "## You Did It!\n\nCongratulations on completing **Building Emotional Resilience**.\n\n### What You Learned:\n\n- What emotional resilience means and the four pillars\n- The 4-7-8 breathing technique for calm\n- The 5-4-3-2-1 grounding technique for anxious moments\n- How to identify your stress patterns\n- When and how to reach out for support\n\n### Your Next Steps:\n\n1. **Practice one technique daily** for the next week\n2. **Complete your resilience plan** if you haven't already\n3. **Share what you learned** with a friend or family member\n4. **Check back in** — resilience grows with practice\n\n### Continue Your Journey:\n\nLook for these related courses:\n- Managing Test Anxiety\n- Mindfulness for Students\n- Building Better Relationships\n\nYou've taken an important step today. Keep building!",
                'key_points' => [
                    'Resilience grows with consistent practice',
                    'Start with just one technique',
                    'Reach out for support when needed',
                ],
            ],
        ]);
    }

    public function down(): void
    {
        // Get the demo school
        $demoSchool = Organization::where('org_name', 'Lincoln High School')->first();

        if ($demoSchool) {
            // Remove the rich demo course
            MiniCourse::where('org_id', $demoSchool->id)
                ->where('title', 'Building Emotional Resilience')
                ->forceDelete();

            // Reset course generation settings
            $demoSchool->update(['course_generation_settings' => null]);
        }

        // Remove system content blocks
        ContentBlock::whereNull('org_id')->forceDelete();

        // Remove system templates
        CourseTemplate::where('is_system', true)->forceDelete();

        // Remove system tags
        ContentTag::whereNull('org_id')->delete();
    }
};
