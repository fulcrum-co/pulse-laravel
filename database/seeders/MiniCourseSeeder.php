<?php

namespace Database\Seeders;

use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseStepProgress;
use App\Models\MiniCourseSuggestion;
use App\Models\Organization;
use App\Models\Provider;
use App\Models\Resource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class MiniCourseSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $school = Organization::first();
        }
        if (! $school) {
            $this->command->error('No organization found. Please seed organizations first.');

            return;
        }

        $admin = User::where('org_id', $school->id)->first();
        if (! $admin) {
            $admin = User::first();
        }
        if (! $admin) {
            $this->command->error('No user found. Please seed users first.');

            return;
        }

        $counselor = User::where('primary_role', 'counselor')->where('org_id', $school->id)->first() ?? $admin;

        // Get some resources and providers to link in courses
        $anxietyResource = Resource::where('category', 'anxiety')->first();
        $mindfulnessResource = Resource::where('category', 'stress')->first();
        $therapist = Provider::where('provider_type', 'therapist')->first();
        $coach = Provider::where('provider_type', 'coach')->first();

        // Course 1: Stress Management (comprehensive example)
        $stressCourse = MiniCourse::create([
            'org_id' => $school->id,
            'title' => 'Stress Management Foundations',
            'description' => 'Learn practical techniques to identify, understand, and manage stress effectively. This course combines education, self-reflection, and actionable strategies.',
            'objectives' => [
                'Understand the physical and emotional signs of stress',
                'Learn 3 evidence-based coping techniques',
                'Create a personalized stress management plan',
                'Identify healthy vs unhealthy coping mechanisms',
            ],
            'rationale' => 'Stress management is a foundational life skill that impacts academic performance, relationships, and overall wellbeing. This course was designed to give students practical tools they can use immediately.',
            'expected_experience' => 'You\'ll watch a short video, complete reflection exercises, practice mindfulness techniques, and create a personal action plan. The course takes about 45 minutes to complete, but you can take breaks and return anytime.',
            'course_type' => MiniCourse::TYPE_WELLNESS,
            'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
            'target_grades' => ['9', '10', '11', '12'],
            'target_risk_levels' => ['low', 'high'],
            'target_needs' => ['stress', 'anxiety', 'coping skills', 'wellness'],
            'estimated_duration_minutes' => 45,
            'status' => MiniCourse::STATUS_ACTIVE,
            'is_template' => true,
            'created_by' => $counselor->id,
            'published_at' => now()->subDays(30),
        ]);

        // Create steps for stress course
        $this->createStressManagementSteps($stressCourse, $mindfulnessResource);

        // Create a version
        $stressCourse->createVersion('Initial published version');

        // Course 2: Study Skills Booster (AI-generated example)
        $studyCourse = MiniCourse::create([
            'org_id' => $school->id,
            'title' => 'Study Skills Booster',
            'description' => 'Discover effective study techniques backed by cognitive science. Learn how your brain learns best and build habits for academic success.',
            'objectives' => [
                'Understand how memory and learning work',
                'Apply spaced repetition and active recall techniques',
                'Create an effective study schedule',
                'Reduce test anxiety through preparation',
            ],
            'rationale' => 'Many students struggle academically not due to lack of ability, but because they haven\'t learned effective study strategies. This course teaches evidence-based techniques that can significantly improve retention and performance.',
            'expected_experience' => 'You\'ll learn about the science of learning through short readings and videos, then practice applying techniques to your own schoolwork. By the end, you\'ll have a personalized study plan.',
            'course_type' => MiniCourse::TYPE_ACADEMIC,
            'creation_source' => MiniCourse::SOURCE_AI_GENERATED,
            'ai_generation_context' => [
                'trigger' => 'gpa_decline',
                'signals' => [
                    'recent_gpa_drop' => 0.5,
                    'attendance_concern' => false,
                    'survey_stress_academic' => 7,
                ],
                'generated_at' => now()->subDays(14)->toISOString(),
            ],
            'target_grades' => ['9', '10', '11', '12'],
            'target_risk_levels' => ['good', 'low'],
            'target_needs' => ['academic skills', 'study habits', 'test prep'],
            'estimated_duration_minutes' => 35,
            'status' => MiniCourse::STATUS_ACTIVE,
            'is_template' => false,
            'created_by' => $admin->id,
            'published_at' => now()->subDays(14),
        ]);

        $this->createStudySkillsSteps($studyCourse, $coach);
        $studyCourse->createVersion('AI-generated course, reviewed and published');

        // Course 3: Building Self-Confidence (draft example)
        $confidenceCourse = MiniCourse::create([
            'org_id' => $school->id,
            'title' => 'Building Self-Confidence',
            'description' => 'Develop a stronger sense of self-worth and learn to believe in your abilities. This course combines psychology insights with practical exercises.',
            'objectives' => [
                'Identify negative self-talk patterns',
                'Practice positive affirmations and reframing',
                'Set and achieve small confidence-building goals',
                'Build a support network',
            ],
            'rationale' => 'Self-confidence is fundamental to trying new things, recovering from setbacks, and reaching your potential. Many teens struggle with self-doubt, and this course provides tools to build authentic confidence.',
            'expected_experience' => 'Through journaling prompts, self-reflection exercises, and small challenges, you\'ll gradually build confidence in a supportive, judgment-free learning experience.',
            'course_type' => MiniCourse::TYPE_SKILL_BUILDING,
            'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
            'target_grades' => ['9', '10', '11', '12'],
            'target_risk_levels' => ['low', 'high'],
            'target_needs' => ['self-esteem', 'confidence', 'personal growth'],
            'estimated_duration_minutes' => 50,
            'status' => MiniCourse::STATUS_DRAFT,
            'is_template' => false,
            'created_by' => $counselor->id,
        ]);

        $this->createConfidenceSteps($confidenceCourse);

        // Course 4: Anger Management Essentials
        $angerCourse = MiniCourse::create([
            'org_id' => $school->id,
            'title' => 'Anger Management Essentials',
            'description' => 'Learn to recognize anger triggers, understand the emotion, and develop healthy ways to express and manage anger.',
            'objectives' => [
                'Recognize physical signs of anger',
                'Identify personal anger triggers',
                'Learn and practice de-escalation techniques',
                'Develop healthy expression strategies',
            ],
            'rationale' => 'Anger is a normal emotion, but without healthy management skills, it can damage relationships and lead to regrettable actions. This course teaches students to work with anger constructively.',
            'expected_experience' => 'You\'ll learn about the anger cycle, practice breathing and grounding techniques, and develop a personal anger management toolkit through interactive exercises.',
            'course_type' => MiniCourse::TYPE_BEHAVIORAL,
            'creation_source' => MiniCourse::SOURCE_HUMAN_CREATED,
            'target_grades' => ['9', '10', '11', '12'],
            'target_risk_levels' => ['low', 'high'],
            'target_needs' => ['anger management', 'emotional regulation', 'behavior'],
            'estimated_duration_minutes' => 40,
            'status' => MiniCourse::STATUS_ACTIVE,
            'is_template' => true,
            'created_by' => $counselor->id,
            'published_at' => now()->subDays(60),
        ]);

        $this->createAngerManagementSteps($angerCourse, $therapist);
        $angerCourse->createVersion('Initial version with therapist connection');

        // Course 5: Goal Setting Workshop (intervention type)
        $goalCourse = MiniCourse::create([
            'org_id' => $school->id,
            'title' => 'Goal Setting Workshop',
            'description' => 'Transform your dreams into achievable goals using proven frameworks. Learn to set SMART goals and create action plans.',
            'objectives' => [
                'Understand the SMART goal framework',
                'Identify meaningful personal and academic goals',
                'Break big goals into manageable steps',
                'Track progress and adjust plans',
            ],
            'rationale' => 'Goal-setting is a critical skill for academic success and life satisfaction. Students who learn to set and pursue meaningful goals show improved motivation and achievement.',
            'expected_experience' => 'Through guided worksheets and reflection prompts, you\'ll identify what matters most to you and create a concrete plan to achieve your goals.',
            'course_type' => MiniCourse::TYPE_INTERVENTION,
            'creation_source' => MiniCourse::SOURCE_HYBRID,
            'target_grades' => ['9', '10', '11', '12'],
            'target_risk_levels' => ['good', 'low', 'high'],
            'target_needs' => ['motivation', 'goal setting', 'planning'],
            'estimated_duration_minutes' => 30,
            'status' => MiniCourse::STATUS_ACTIVE,
            'is_template' => true,
            'created_by' => $admin->id,
            'published_at' => now()->subDays(45),
        ]);

        $this->createGoalSettingSteps($goalCourse);
        $goalCourse->createVersion('Published version with reflection prompts');

        // Create enrollments for some students
        $this->createSampleEnrollments($school, [$stressCourse, $studyCourse, $angerCourse, $goalCourse], $counselor);

        // Create course suggestions for high-risk students
        $this->createSampleSuggestions($school, [$stressCourse, $studyCourse, $angerCourse, $goalCourse]);
    }

    private function createStressManagementSteps(MiniCourse $course, ?Resource $resource): void
    {
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Understanding Stress',
            'description' => 'Learn what stress is and how it affects your mind and body.',
            'instructions' => 'Read through this introduction to understand the basics of stress and why it matters.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# What is Stress?\n\nStress is your body's natural response to challenges or demands. While some stress can be motivating (like before a big game or presentation), too much stress can affect your health, mood, and performance.\n\n## Physical Signs of Stress\n- Headaches or muscle tension\n- Fatigue or trouble sleeping\n- Upset stomach\n- Racing heart\n\n## Emotional Signs of Stress\n- Feeling overwhelmed\n- Irritability or mood swings\n- Difficulty concentrating\n- Anxiety or worry\n\nThe good news? Stress management is a skill you can learn!",
                'key_points' => [
                    'Stress is a natural response - everyone experiences it',
                    'Some stress (eustress) can be helpful and motivating',
                    'Chronic stress affects both your body and mind',
                    'Stress management is a learnable skill',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Watch: The Science of Stress',
            'description' => 'A TED-Ed video explaining how stress affects your brain.',
            'instructions' => 'Watch this video to understand the science behind stress and how it impacts your brain.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/WuyPuH9ojCE',
                'body' => "This TED-Ed video by Madhumita Murgia explains how chronic stress affects your brain's size, structure, and function.\n\nAs you watch, pay attention to:\n- How the stress hormone cortisol affects your brain\n- The difference between short-term and chronic stress\n- How stress can actually change your brain's wiring",
                'key_points' => [
                    'Cortisol can damage brain cells in the hippocampus',
                    'Chronic stress shrinks the prefrontal cortex (decision-making)',
                    'The amygdala (fear center) grows with chronic stress',
                    'These brain changes can be reversed with stress management',
                ],
            ],
            'resource_id' => $resource?->id,
            'estimated_duration_minutes' => 7,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'Identify Your Stress Signals',
            'description' => 'Reflect on how stress shows up in your life.',
            'instructions' => 'Take a moment to think about recent stressful situations. Answer the questions below honestly - there are no right or wrong answers.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Understanding your personal stress signals is the first step to managing them effectively. Everyone experiences stress differently.\n\nTake your time with these reflections - the more honest you are, the more helpful this will be.",
                'prompts' => [
                    'What physical signs do you notice when you\'re stressed? (headaches, tight shoulders, stomach aches, etc.)',
                    'What situations at school tend to stress you out the most?',
                    'How do you currently cope with stress? (Be honest - no judgment!)',
                    'On a scale of 1-10, how stressed have you felt this past week?',
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
            'feedback_prompt' => 'Your responses help us understand your stress patterns. A counselor may follow up if you\'d like to talk more.',
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 4,
            'step_type' => MiniCourseStep::TYPE_PRACTICE,
            'title' => 'Try: Box Breathing',
            'description' => 'Practice a simple breathing technique used by Navy SEALs.',
            'instructions' => "Box breathing (also called square breathing) is used by Navy SEALs, athletes, and first responders to stay calm under pressure. It's simple, free, and you can do it anywhere.",
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'body' => "Box breathing works by activating your parasympathetic nervous system - the part that helps you rest and relax. When stressed, your sympathetic nervous system (fight or flight) takes over. This exercise shifts you back to calm.\n\n**Tips for success:**\n- Find a comfortable position\n- Close your eyes if you can\n- Focus only on your breath\n- Don't worry if your mind wanders",
                'activity' => [
                    'title' => 'Box Breathing Exercise',
                    'instructions' => 'Follow the rhythm below. Each phase is 4 seconds. Complete 4 full cycles.',
                    'steps' => [
                        ['duration' => 4, 'action' => 'Breathe In'],
                        ['duration' => 4, 'action' => 'Hold'],
                        ['duration' => 4, 'action' => 'Breathe Out'],
                        ['duration' => 4, 'action' => 'Hold'],
                    ],
                    'cycles' => 4,
                ],
                'key_points' => [
                    'Box breathing activates your parasympathetic nervous system',
                    'Use it anywhere - before tests, during arguments, when anxious',
                    'Regular practice makes it more effective',
                    'Even one cycle can help in a stressful moment',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 5,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Healthy vs. Unhealthy Coping',
            'description' => 'Learn to distinguish between coping strategies that help and those that hurt.',
            'instructions' => 'Review this comparison of healthy and unhealthy coping mechanisms.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Coping Strategies Comparison\n\n## Healthy Coping ✅\n- Exercise or physical activity\n- Talking to someone you trust\n- Deep breathing or meditation\n- Getting enough sleep\n- Breaking tasks into smaller steps\n- Spending time in nature\n- Creative expression (art, music, writing)\n\n## Unhealthy Coping ❌\n- Avoiding problems/procrastinating\n- Excessive screen time or gaming\n- Isolating from friends/family\n- Using substances (alcohol, drugs, vaping)\n- Oversleeping or undersleeping\n- Taking stress out on others\n- Self-harm\n\n**Remember:** It's okay to occasionally use avoidance strategies, but if they become your primary coping method, they can make stress worse.",
                'key_points' => [
                    'Healthy coping builds resilience over time',
                    'Unhealthy coping provides short-term relief but long-term harm',
                    'Start small - one healthy habit at a time',
                    'If using unhealthy coping regularly, please talk to someone',
                ],
                'downloads' => [
                    [
                        'title' => 'Coping Strategies Worksheet',
                        'type' => 'pdf',
                        'size' => '245 KB',
                    ],
                    [
                        'title' => 'Weekly Mood & Stress Tracker',
                        'type' => 'pdf',
                        'size' => '180 KB',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 6,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Create Your Stress Plan',
            'description' => 'Build a personalized plan for managing stress.',
            'instructions' => 'Based on what you\'ve learned, create your personal stress management plan by answering these questions.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Now it's time to create YOUR personal stress management toolkit. There's no one-size-fits-all approach - the best plan works for your life, schedule, and triggers.\n\nBe specific in your answers. Instead of \"exercise more,\" try \"walk for 15 minutes after school on Mondays, Wednesdays, and Fridays.\"",
                'prompts' => [
                    'List 3 healthy coping strategies you will try this week (be specific about when and how):',
                    'Who are 2-3 people you can talk to when feeling stressed?',
                    'What\'s your early warning sign that stress is building up?',
                    'What will you do if your usual strategies aren\'t working?',
                ],
                'resources' => [
                    [
                        'title' => 'Crisis Text Line',
                        'description' => 'Text HOME to 741741 for free, 24/7 crisis support',
                        'url' => 'https://www.crisistextline.org/',
                    ],
                    [
                        'title' => 'School Counseling Office',
                        'description' => 'Schedule a free appointment with your school counselor',
                    ],
                    [
                        'title' => 'Teen Line',
                        'description' => 'Call 1-800-852-8336 (6pm-10pm PT) to talk to a trained teen',
                        'url' => 'https://teenlineonline.org/',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
            'completion_criteria' => ['all_prompts_answered' => true],
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 7,
            'step_type' => MiniCourseStep::TYPE_CHECKPOINT,
            'title' => 'Course Completion',
            'description' => 'Congratulations on completing the Stress Management Foundations course!',
            'instructions' => 'Review what you\'ve learned and let us know how this course helped you.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Great job completing this course! 🎉\n\nYou've taken an important step in learning to manage stress. Remember, these skills take practice - don't expect to master them overnight.\n\nConsider practicing box breathing daily, even when you're not stressed. The more you practice, the more automatic it becomes.",
                'summary' => [
                    'You learned what stress is and how it affects your brain and body',
                    'You identified your personal stress signals and triggers',
                    'You practiced box breathing - a powerful calming technique',
                    'You learned the difference between healthy and unhealthy coping',
                    'You created a personal stress management plan',
                ],
                'feedback_questions' => [
                    'How helpful was this course? (1-5)',
                    'What was the most useful part?',
                    'What would you add or change?',
                    'Would you recommend this course to a friend?',
                ],
            ],
            'estimated_duration_minutes' => 3,
            'is_required' => true,
        ]);
    }

    private function createStudySkillsSteps(MiniCourse $course, ?Provider $coach): void
    {
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'How Your Brain Learns',
            'description' => 'Understanding the science of learning and memory.',
            'instructions' => 'Read about how your brain processes and retains information.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# The Science of Learning\n\nYour brain is incredibly powerful, but it doesn't work like a computer that simply stores information. Understanding how memory works can help you study smarter, not harder.\n\n## Key Concepts\n\n**Encoding** - How information gets into your brain\n**Storage** - How information is kept in your brain\n**Retrieval** - How you access stored information\n\n## The Forgetting Curve\n\nWithout review, you forget about 70% of new information within 24 hours! But with strategic review, you can dramatically improve retention.",
                'key_points' => [
                    'Your brain has three memory stages: encoding, storage, retrieval',
                    'You forget 70% of new info within 24 hours without review',
                    'Strategic review timing dramatically improves retention',
                    'Study smarter, not harder - technique matters more than time',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Watch: How to Study Effectively',
            'description' => 'A TED-Ed video on evidence-based study techniques.',
            'instructions' => 'Watch this video about the science of effective studying.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/p60rN9JEapg',
                'body' => "This video from TED-Ed explains why some study methods work better than others based on cognitive science research.\n\nPay attention to:\n- Why re-reading is one of the least effective methods\n- How testing yourself beats passive review\n- The importance of spacing out your study sessions",
                'key_points' => [
                    'Re-reading creates an \"illusion of competence\"',
                    'Testing yourself (active recall) strengthens memory',
                    'Spacing study sessions beats cramming every time',
                    'Mixing up topics (interleaving) improves learning',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_PRACTICE,
            'title' => 'Try Active Recall Now',
            'description' => 'Practice active recall with what you just learned.',
            'instructions' => 'Without looking back, try to answer these questions. This is active recall in action!',
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'instructions' => 'Test your understanding of what you just learned. Don\'t worry about getting everything right - the effort of trying to remember is what builds stronger memories!',
                'questions' => [
                    [
                        'id' => 'q1',
                        'question' => 'What are the three stages of memory?',
                        'type' => 'free_response',
                    ],
                    [
                        'id' => 'q2',
                        'question' => 'What percentage of information do we typically forget within 24 hours without review?',
                        'type' => 'multiple_choice',
                        'options' => ['30%', '50%', '70%', '90%'],
                    ],
                    [
                        'id' => 'q3',
                        'question' => 'How confident are you that you can use active recall in your studying?',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Not confident',
                        'max_label' => 'Very confident',
                    ],
                ],
                'key_points' => [
                    'Notice how trying to remember felt effortful? That\'s good!',
                    'The struggle to recall actually strengthens your memory',
                    'Do this with all your study material for better retention',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 4,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Spaced Repetition',
            'description' => 'Learn how to schedule your review sessions for maximum retention.',
            'instructions' => 'Understand the power of spacing out your study sessions.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Spaced Repetition\n\nInstead of cramming, spread your study sessions over time.\n\n## Optimal Review Schedule\n- **Day 1:** Learn new material\n- **Day 2:** First review (brief - 10 min)\n- **Day 4:** Second review\n- **Day 7:** Third review\n- **Day 14:** Fourth review\n- **Day 30:** Long-term review\n\n## Tools That Help\n- **Anki** - Free flashcard app with built-in spacing\n- **Quizlet** - Has spaced repetition mode\n- **RemNote** - Combined notes and flashcards\n- **Paper calendar** - For planning review sessions\n\n**Remember:** Short, frequent sessions beat long cramming sessions every time!",
                'key_points' => [
                    'Cramming doesn\'t work for long-term retention',
                    'Spread reviews: Day 1, 2, 4, 7, 14, 30',
                    'Each review can be short (10-15 minutes)',
                    'Apps like Anki automate the scheduling for you',
                ],
                'downloads' => [
                    [
                        'title' => 'Spaced Repetition Study Planner',
                        'type' => 'pdf',
                        'size' => '320 KB',
                    ],
                    [
                        'title' => 'Anki Quick Start Guide',
                        'type' => 'pdf',
                        'size' => '450 KB',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 5,
            'step_type' => MiniCourseStep::TYPE_HUMAN_CONNECTION,
            'title' => 'Optional: Study Coaching Session',
            'description' => 'Consider scheduling a session with a study skills coach.',
            'instructions' => 'If you\'d like personalized help creating your study system, consider connecting with one of our learning coaches.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Sometimes it helps to have personalized guidance. Our learning coaches can help you:\n\n- Create a custom study schedule for your classes\n- Choose the right tools for your learning style\n- Troubleshoot what's not working\n- Stay accountable to your study plan",
                'recommendation' => 'A coach can help you apply these techniques to your specific classes and learning style.',
                'resources' => [
                    [
                        'title' => 'Academic Support Center',
                        'description' => 'Free tutoring and study skills coaching for all students',
                    ],
                    [
                        'title' => 'Peer Tutoring Program',
                        'description' => 'Get help from high-achieving students in your subjects',
                    ],
                    [
                        'title' => 'Khan Academy',
                        'description' => 'Free online courses and practice in every subject',
                        'url' => 'https://www.khanacademy.org/',
                    ],
                ],
            ],
            'provider_id' => $coach?->id,
            'estimated_duration_minutes' => 3,
            'is_required' => false,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 6,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Create Your Study Schedule',
            'description' => 'Plan your study sessions for the upcoming week.',
            'instructions' => 'Using what you\'ve learned, create a realistic study plan for one subject.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Time to put these techniques into practice! Pick ONE subject to start with - you can expand to others once you've got the hang of it.\n\nBe realistic about your schedule. It's better to plan 15 minutes that you'll actually do than an hour that you'll skip.",
                'prompts' => [
                    'Which subject will you focus on first and why?',
                    'When will you have your initial study session? (Be specific: day and time)',
                    'Schedule your 4 review sessions (Day 2, 4, 7, 14) - what days/times?',
                    'What active recall techniques will you use? (flashcards, practice problems, teaching someone, etc.)',
                ],
                'key_points' => [
                    'Start with just ONE subject to build the habit',
                    'Schedule specific times, not just \"sometime this week\"',
                    'Set phone reminders for your review sessions',
                    'Track your results to see what works best for you',
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
            'completion_criteria' => ['schedule_created' => true],
        ]);
    }

    private function createConfidenceSteps(MiniCourse $course): void
    {
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'What is Self-Confidence?',
            'description' => 'Understanding true confidence vs. arrogance.',
            'instructions' => 'Learn about what self-confidence really means.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/w-HYZv6HzAs',
                'body' => "# Understanding Self-Confidence\n\nSelf-confidence is believing in your ability to handle challenges and learn from mistakes. It's NOT:\n- Being perfect\n- Never feeling afraid\n- Thinking you're better than others\n\n**True confidence** means knowing you have value as a person, regardless of your achievements or what others think.\n\nWatch this TED Talk about the skill of self-confidence.",
                'key_points' => [
                    'Confidence is a skill that can be developed',
                    'True confidence comes from within, not from achievements',
                    'Confident people still feel fear - they just act anyway',
                    'Self-worth is not the same as self-esteem',
                ],
            ],
            'estimated_duration_minutes' => 15,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'Your Confidence Assessment',
            'description' => 'Reflect on your current level of self-confidence.',
            'instructions' => 'Answer these questions honestly to understand where you are now.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Self-awareness is the first step to building confidence. There's no judgment here - just honest reflection about where you are right now.\n\nEveryone has areas where they feel more or less confident. Understanding your patterns helps you know where to focus.",
                'prompts' => [
                    'On a scale of 1-10, how confident do you feel generally?',
                    'In what situations do you feel most confident? What makes those different?',
                    'When do you feel least confident? What triggers those feelings?',
                    'What negative thoughts do you often have about yourself?',
                ],
                'questions' => [
                    [
                        'id' => 'confidence_scale',
                        'question' => 'Overall, how confident do you feel in yourself?',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Not at all confident',
                        'max_label' => 'Very confident',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Challenging Negative Self-Talk',
            'description' => 'Learn to recognize and reframe negative thoughts.',
            'instructions' => 'Discover techniques for changing your inner dialogue.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Reframing Negative Self-Talk\n\nThe voice in your head can be your biggest critic. Learning to challenge negative self-talk is a key confidence skill.\n\n## Common Thinking Patterns (Cognitive Distortions)\n- **All-or-nothing:** \"I failed the test, I'm stupid\"\n- **Mind reading:** \"Everyone thinks I'm weird\"\n- **Fortune telling:** \"I'll definitely mess this up\"\n- **Catastrophizing:** \"If I fail, my life is over\"\n- **Discounting positives:** \"That doesn't count, anyone could do that\"\n\n## Reframing Examples\n| Negative Thought | Reframe |\n|-----------------|----------|\n| \"I'm stupid\" | \"I'm still learning this subject\" |\n| \"Everyone hates me\" | \"Some people like me, and that's what matters\" |\n| \"I can't do anything right\" | \"I'm good at some things and learning others\" |\n| \"I'll definitely fail\" | \"I don't know the outcome yet - let me try my best\" |",
                'key_points' => [
                    'Negative self-talk is often automatic and exaggerated',
                    'You can learn to catch and challenge these thoughts',
                    'Reframing isn\'t about being fake-positive',
                    'It\'s about being fair and accurate to yourself',
                ],
                'downloads' => [
                    [
                        'title' => 'Thought Journal Worksheet',
                        'type' => 'pdf',
                        'size' => '210 KB',
                    ],
                    [
                        'title' => 'Cognitive Distortions Cheat Sheet',
                        'type' => 'pdf',
                        'size' => '150 KB',
                    ],
                ],
                'resources' => [
                    [
                        'title' => 'School Counseling Office',
                        'description' => 'Talk to a counselor about building self-confidence',
                    ],
                    [
                        'title' => 'Teen Mental Health Resources',
                        'description' => 'Additional resources for teens struggling with self-esteem',
                        'url' => 'https://www.jedfoundation.org/mental-health-resource-center/',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 7,
            'is_required' => true,
        ]);
    }

    private function createAngerManagementSteps(MiniCourse $course, ?Provider $therapist): void
    {
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Understanding Anger',
            'description' => 'Learn why anger is normal and how it works.',
            'instructions' => 'Read about the anger response and why we all experience it.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/BsVq5R_F6RA',
                'body' => "# Anger: A Normal Emotion\n\nAnger is a natural response to perceived threats, injustice, or frustration. It's not bad to feel angry - it's what we DO with anger that matters.\n\n## The Anger Cycle\n1. **Trigger** - Something happens\n2. **Thoughts** - We interpret the situation\n3. **Physical response** - Body prepares for action\n4. **Behavior** - How we express the anger\n\nUnderstanding this cycle gives us points where we can intervene.\n\nWatch this video about what anger does to your brain and body.",
                'key_points' => [
                    'Anger is a normal, healthy emotion - everyone feels it',
                    'It\'s not the feeling that\'s the problem, it\'s the behavior',
                    'The anger cycle has 4 stages: trigger, thoughts, physical, behavior',
                    'You can intervene at any point in the cycle',
                ],
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'Your Anger Triggers',
            'description' => 'Identify what situations typically make you angry.',
            'instructions' => 'Reflect on recent times you felt angry.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Understanding your personal anger triggers is key to managing them. Common triggers include:\n\n- Feeling disrespected or unfairly treated\n- Being interrupted or not listened to\n- Feeling controlled or powerless\n- Physical discomfort (tired, hungry, in pain)\n- Stress overload\n\nThink about a recent time you felt angry and walk through what happened.",
                'prompts' => [
                    'Describe a recent situation that made you angry - what happened?',
                    'What physical signs did you notice in your body? (clenched fists, racing heart, heat, etc.)',
                    'How did you respond? Was it helpful or unhelpful?',
                    'Looking back, what do you wish you had done differently?',
                ],
                'questions' => [
                    [
                        'id' => 'anger_frequency',
                        'question' => 'How often do you feel intense anger that\'s hard to control?',
                        'type' => 'scale',
                        'min' => 1,
                        'max' => 5,
                        'min_label' => 'Rarely',
                        'max_label' => 'Daily',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_PRACTICE,
            'title' => 'Grounding Technique: 5-4-3-2-1',
            'description' => 'Practice a technique to calm down when anger rises.',
            'instructions' => 'When you feel anger building, use your senses to ground yourself in the present moment. This interrupts the anger cycle.',
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'body' => "The 5-4-3-2-1 grounding technique uses your senses to bring you back to the present moment. When anger is rising, your brain goes into \"fight mode\" - grounding helps you regain control.\n\nPractice this now, even though you're calm. The more you practice when calm, the easier it is to use when angry.",
                'activity' => [
                    'title' => '5-4-3-2-1 Sensory Grounding',
                    'instructions' => 'Go through each sense, taking your time. Say the items out loud or write them down.',
                    'steps' => [
                        ['duration' => 30, 'action' => '5 things you SEE'],
                        ['duration' => 30, 'action' => '4 things you TOUCH'],
                        ['duration' => 20, 'action' => '3 things you HEAR'],
                        ['duration' => 15, 'action' => '2 things you SMELL'],
                        ['duration' => 10, 'action' => '1 thing you TASTE'],
                    ],
                    'cycles' => 1,
                ],
                'key_points' => [
                    'Grounding interrupts the anger escalation cycle',
                    'It works by engaging your prefrontal cortex (thinking brain)',
                    'Practice when calm so it becomes automatic',
                    'Use it the moment you notice anger building',
                ],
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 4,
            'step_type' => MiniCourseStep::TYPE_HUMAN_CONNECTION,
            'title' => 'Consider: Talking to a Professional',
            'description' => 'Ongoing anger issues may benefit from professional support.',
            'instructions' => 'If anger is frequently affecting your relationships or wellbeing, talking to a professional can help develop personalized strategies.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Sometimes anger management needs more support than a course can provide. Consider reaching out if:\n\n- You feel angry most of the time\n- Your anger has hurt relationships\n- You've gotten physical when angry\n- People say they're scared of you when you're mad\n- You feel out of control when angry\n\n**There's no shame in getting help.** Learning to manage anger is a sign of strength, not weakness.",
                'recommendation' => 'A therapist can help you develop personalized strategies for managing intense anger.',
                'resources' => [
                    [
                        'title' => 'School Counseling Office',
                        'description' => 'Free, confidential support from trained counselors',
                    ],
                    [
                        'title' => 'Crisis Text Line',
                        'description' => 'Text HOME to 741741 if you\'re in crisis',
                        'url' => 'https://www.crisistextline.org/',
                    ],
                    [
                        'title' => 'SAMHSA National Helpline',
                        'description' => '1-800-662-4357 - Free, confidential, 24/7 support',
                        'url' => 'https://www.samhsa.gov/find-help/national-helpline',
                    ],
                ],
            ],
            'provider_id' => $therapist?->id,
            'estimated_duration_minutes' => 3,
            'is_required' => false,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 5,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Your Anger Management Plan',
            'description' => 'Create a personal plan for handling anger.',
            'instructions' => 'Based on what you\'ve learned, create your anger management toolkit.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Having a plan BEFORE you get angry makes it much easier to use in the moment. Think of this as your personal anger toolkit.\n\nThe key is to act early - the longer you wait, the harder it is to de-escalate.",
                'prompts' => [
                    'My early warning signs of anger are: (physical sensations, thoughts, situations)',
                    'When I first notice anger building, I will: (specific actions)',
                    'If I\'m too angry to think clearly, I will: (exit strategy)',
                    'After I calm down, I will: (repair relationships, reflect on what happened)',
                ],
                'key_points' => [
                    'Act at the FIRST sign of anger, not when you\'re already furious',
                    'Having an exit strategy prevents regrettable actions',
                    'It\'s okay to say \"I need a break\" and leave a situation',
                    'Repairing relationships after anger is important',
                ],
                'downloads' => [
                    [
                        'title' => 'Anger Management Plan Template',
                        'type' => 'pdf',
                        'size' => '275 KB',
                    ],
                    [
                        'title' => 'Anger Warning Signs Checklist',
                        'type' => 'pdf',
                        'size' => '120 KB',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);
    }

    private function createGoalSettingSteps(MiniCourse $course): void
    {
        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 1,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'The SMART Framework',
            'description' => 'Learn how to set goals that actually work.',
            'instructions' => 'Discover the proven SMART goal framework.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://www.youtube.com/embed/1-SvuFIQjK8',
                'body' => "# SMART Goals\n\nThe SMART framework turns vague wishes into concrete plans. Watch this video, then review the framework below.\n\n**S** - Specific: Clear and well-defined\n**M** - Measurable: You can track progress\n**A** - Achievable: Challenging but realistic\n**R** - Relevant: Matters to YOU\n**T** - Time-bound: Has a deadline\n\n## Example\n❌ \"I want to get better grades\"\n✅ \"I will raise my math grade from B to A by the end of the semester by studying 30 minutes daily and attending tutoring once a week\"",
                'key_points' => [
                    'Vague goals rarely get achieved',
                    'SMART goals are specific, measurable, achievable, relevant, and time-bound',
                    'Writing goals down increases success rate by 42%',
                    'Break big goals into smaller milestones',
                ],
                'downloads' => [
                    [
                        'title' => 'SMART Goal Worksheet',
                        'type' => 'pdf',
                        'size' => '185 KB',
                    ],
                    [
                        'title' => 'Goal Tracking Template',
                        'type' => 'pdf',
                        'size' => '220 KB',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 8,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_REFLECTION,
            'title' => 'What Matters to You?',
            'description' => 'Identify areas where you want to grow.',
            'instructions' => 'Before setting goals, reflect on what\'s important to you.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Goals work best when they connect to what you truly care about. Take a moment to think about different areas of your life:\n\n- **Academic:** Grades, skills, college prep\n- **Social:** Friendships, relationships, communication\n- **Health:** Physical fitness, sleep, mental health\n- **Personal:** Hobbies, creativity, self-improvement\n- **Future:** Career, independence, life skills\n\nThere are no wrong answers here - this is about discovering what matters to YOU.",
                'prompts' => [
                    'What do you want your life to look like in 1 year? Be specific.',
                    'What\'s one thing you wish was different about school right now?',
                    'What skill would make the biggest difference in your life if you developed it?',
                    'What achievement would make you genuinely proud of yourself?',
                ],
                'questions' => [
                    [
                        'id' => 'goal_area',
                        'question' => 'Which area of your life do you most want to improve?',
                        'type' => 'multiple_choice',
                        'options' => ['Academic/School', 'Social/Relationships', 'Health/Wellness', 'Personal Growth', 'Future/Career'],
                    ],
                ],
            ],
            'estimated_duration_minutes' => 10,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_ACTION,
            'title' => 'Write Your SMART Goal',
            'description' => 'Create one SMART goal based on your reflections.',
            'instructions' => 'Using the SMART framework, write out a complete goal. Be as specific as possible.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "Now it's time to write your SMART goal. Use your reflections from the previous step.\n\n**Remember:**\n- Start with ONE goal (you can add more later)\n- Make it challenging but achievable\n- Be specific about the steps you'll take\n- Set a clear deadline\n\n**Example SMART Goal:**\n\"I will improve my public speaking skills by joining the debate club, practicing one speech per week, and presenting at least once in each class by the end of this semester.\"",
                'prompts' => [
                    'Specific - What exactly do you want to achieve? (What, who, where, which, why)',
                    'Measurable - How will you know you\'ve succeeded? What numbers or evidence will show progress?',
                    'Achievable - What specific steps will you take? List 3-5 actions.',
                    'Relevant - Why does this matter to you? How does it connect to your bigger vision?',
                    'Time-bound - By when will you achieve this? Set a specific date.',
                ],
                'key_points' => [
                    'Write your goal somewhere you\'ll see it daily',
                    'Tell someone about your goal for accountability',
                    'Break it into weekly milestones',
                    'Review and adjust monthly - goals can evolve',
                ],
                'resources' => [
                    [
                        'title' => 'Academic Support Center',
                        'description' => 'Get help with academic goal planning',
                    ],
                    [
                        'title' => 'College & Career Center',
                        'description' => 'Support for future planning and career goals',
                    ],
                ],
            ],
            'estimated_duration_minutes' => 15,
            'is_required' => true,
            'completion_criteria' => ['goal_written' => true],
        ]);
    }

    private function createSampleEnrollments(Organization $school, array $courses, User $counselor): void
    {
        $students = Student::where('org_id', $school->id)->take(10)->get();

        if ($students->isEmpty()) {
            return; // No students to enroll
        }

        foreach ($students as $index => $student) {
            // Enroll some students in courses with varying progress
            $course = $courses[$index % count($courses)];
            if ($course->status !== MiniCourse::STATUS_ACTIVE) {
                continue;
            }

            // Skip if already enrolled (prevents duplicate key error)
            if (MiniCourseEnrollment::where('mini_course_id', $course->id)->where('student_id', $student->id)->exists()) {
                continue;
            }

            $status = collect([
                MiniCourseEnrollment::STATUS_IN_PROGRESS,
                MiniCourseEnrollment::STATUS_COMPLETED,
                MiniCourseEnrollment::STATUS_ENROLLED,
            ])->random();

            $enrollment = MiniCourseEnrollment::create([
                'mini_course_id' => $course->id,
                'mini_course_version_id' => $course->current_version_id,
                'student_id' => $student->id,
                'enrolled_by' => $counselor->id,
                'enrollment_source' => collect([
                    MiniCourseEnrollment::SOURCE_MANUAL,
                    MiniCourseEnrollment::SOURCE_AI_SUGGESTED,
                    MiniCourseEnrollment::SOURCE_RULE_TRIGGERED,
                ])->random(),
                'status' => $status,
                'progress_percent' => match ($status) {
                    MiniCourseEnrollment::STATUS_COMPLETED => 100,
                    MiniCourseEnrollment::STATUS_IN_PROGRESS => rand(20, 80),
                    default => 0,
                },
                'started_at' => $status !== MiniCourseEnrollment::STATUS_ENROLLED ? now()->subDays(rand(1, 14)) : null,
                'completed_at' => $status === MiniCourseEnrollment::STATUS_COMPLETED ? now()->subDays(rand(1, 7)) : null,
            ]);

            // Create step progress for in-progress and completed enrollments
            if ($status !== MiniCourseEnrollment::STATUS_ENROLLED) {
                $steps = $course->steps()->orderBy('sort_order')->get();
                $completedSteps = $status === MiniCourseEnrollment::STATUS_COMPLETED
                    ? $steps->count()
                    : (int) ($steps->count() * ($enrollment->progress_percent / 100));

                foreach ($steps->take($completedSteps) as $step) {
                    // Skip if progress already exists (prevents duplicate key error)
                    if (MiniCourseStepProgress::where('enrollment_id', $enrollment->id)->where('step_id', $step->id)->exists()) {
                        continue;
                    }

                    MiniCourseStepProgress::create([
                        'enrollment_id' => $enrollment->id,
                        'step_id' => $step->id,
                        'status' => MiniCourseStepProgress::STATUS_COMPLETED,
                        'started_at' => now()->subDays(rand(2, 14)),
                        'completed_at' => now()->subDays(rand(1, 7)),
                        'time_spent_seconds' => rand(60, 600),
                    ]);
                }

                // Set current step for in-progress enrollments
                if ($status === MiniCourseEnrollment::STATUS_IN_PROGRESS && $completedSteps < $steps->count()) {
                    $enrollment->update(['current_step_id' => $steps[$completedSteps]->id]);
                }
            }
        }
    }

    private function createSampleSuggestions(Organization $school, array $courses): void
    {
        $highRiskStudents = Student::where('org_id', $school->id)
            ->where('risk_level', 'high')
            ->take(5)
            ->get();

        if ($highRiskStudents->isEmpty()) {
            return; // No high-risk students for suggestions
        }

        foreach ($highRiskStudents as $student) {
            $course = $courses[array_rand($courses)];
            if ($course->status !== MiniCourse::STATUS_ACTIVE) {
                continue;
            }

            // Skip if suggestion already exists (prevents duplicate key error)
            if (MiniCourseSuggestion::where('contact_type', Student::class)
                ->where('contact_id', $student->id)
                ->where('mini_course_id', $course->id)
                ->exists()) {
                continue;
            }

            MiniCourseSuggestion::create([
                'org_id' => $school->id,
                'contact_type' => Student::class,
                'contact_id' => $student->id,
                'mini_course_id' => $course->id,
                'suggestion_source' => MiniCourseSuggestion::SOURCE_AI_GENERATED,
                'relevance_score' => rand(75, 98) / 100,
                'trigger_signals' => [
                    'risk_level' => 'high',
                    'recent_survey_score' => rand(3, 5),
                    'attendance_rate' => rand(70, 85),
                ],
                'ai_rationale' => "Based on {$student->full_name}'s recent assessment results and behavioral patterns, this course could help address identified areas of concern. The student's profile matches the target audience for this intervention.",
                'ai_explanation' => [
                    'primary_signals' => ['elevated risk score', 'survey responses indicating stress'],
                    'similar_student_outcomes' => '78% of similar students showed improvement after completing this course',
                    'expected_benefits' => ['improved coping skills', 'reduced stress indicators'],
                ],
                'intended_outcomes' => [
                    'Develop practical coping strategies',
                    'Reduce stress and anxiety symptoms',
                    'Improve self-awareness',
                ],
                'status' => MiniCourseSuggestion::STATUS_PENDING,
            ]);
        }
    }
}
