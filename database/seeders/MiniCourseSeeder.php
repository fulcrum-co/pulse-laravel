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
        if (!$school) {
            $school = Organization::first();
        }
        if (!$school) {
            $this->command->error('No organization found. Please seed organizations first.');
            return;
        }

        $admin = User::where('org_id', $school->id)->first();
        if (!$admin) {
            $admin = User::first();
        }
        if (!$admin) {
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
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Watch: The Science of Stress',
            'description' => 'A short video explaining how stress works in your brain and body.',
            'instructions' => 'Watch this 5-minute video to understand the science behind stress.',
            'content_type' => MiniCourseStep::CONTENT_VIDEO,
            'content_data' => [
                'video_url' => 'https://example.com/stress-science-video',
                'video_duration' => 300,
                'transcript_available' => true,
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
                'prompts' => [
                    'What physical signs do you notice when you\'re stressed?',
                    'What situations at school tend to stress you out the most?',
                    'How do you currently cope with stress? (Be honest!)',
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
            'description' => 'Practice a simple breathing technique you can use anywhere.',
            'instructions' => "Follow along with this guided breathing exercise. Box breathing helps activate your body's relaxation response.\n\n1. Breathe IN for 4 seconds\n2. HOLD for 4 seconds\n3. Breathe OUT for 4 seconds\n4. HOLD for 4 seconds\n\nRepeat 4 times.",
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'activity_type' => 'breathing_exercise',
                'duration_seconds' => 120,
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
                'body' => "# Coping Strategies Comparison\n\n## Healthy Coping ✅\n- Exercise or physical activity\n- Talking to someone you trust\n- Deep breathing or meditation\n- Getting enough sleep\n- Breaking tasks into smaller steps\n- Spending time in nature\n\n## Unhealthy Coping ❌\n- Avoiding problems/procrastinating\n- Excessive screen time\n- Isolating from friends/family\n- Using substances\n- Oversleeping\n- Taking stress out on others\n\n**Remember:** It's okay to occasionally use avoidance strategies, but if they become your primary coping method, they can make stress worse in the long run.",
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
                'prompts' => [
                    'List 3 healthy coping strategies you will try this week:',
                    'Who can you talk to when you\'re feeling stressed?',
                    'What\'s one thing you can do differently when you notice stress building?',
                    'What will you do if your usual strategies aren\'t working?',
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
                'summary' => [
                    'You learned what stress is and how it affects you',
                    'You identified your personal stress signals',
                    'You practiced box breathing',
                    'You created a personal stress management plan',
                ],
                'feedback_questions' => [
                    'How helpful was this course? (1-5)',
                    'What was the most useful part?',
                    'What would you add or change?',
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
            ],
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 2,
            'step_type' => MiniCourseStep::TYPE_CONTENT,
            'title' => 'Active Recall: The #1 Study Technique',
            'description' => 'Learn why testing yourself is more effective than re-reading.',
            'instructions' => 'Discover the power of active recall and how to use it.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Active Recall\n\n**Active recall** means actively trying to remember information rather than passively reviewing it.\n\n## Why It Works\n- Strengthens neural pathways\n- Identifies gaps in knowledge\n- More efficient than re-reading\n\n## How to Practice\n1. **Flashcards** - But flip them and try to recall before looking\n2. **Practice questions** - Before checking notes\n3. **Teach someone else** - Explaining forces recall\n4. **Close your notes** - Try to write down everything you remember\n\n**The key:** It should feel effortful! That mental strain is actually building stronger memories.",
            ],
            'estimated_duration_minutes' => 7,
            'is_required' => true,
        ]);

        MiniCourseStep::create([
            'mini_course_id' => $course->id,
            'sort_order' => 3,
            'step_type' => MiniCourseStep::TYPE_PRACTICE,
            'title' => 'Try Active Recall Now',
            'description' => 'Practice active recall with what you just learned.',
            'instructions' => 'Without looking back, try to answer these questions about what you just read.',
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'quiz' => [
                    [
                        'question' => 'What are the three stages of memory?',
                        'type' => 'free_response',
                    ],
                    [
                        'question' => 'What percentage of information do we typically forget within 24 hours without review?',
                        'type' => 'multiple_choice',
                        'options' => ['30%', '50%', '70%', '90%'],
                        'correct' => 2,
                    ],
                    [
                        'question' => 'Why is active recall more effective than re-reading?',
                        'type' => 'free_response',
                    ],
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
                'body' => "# Spaced Repetition\n\nInstead of cramming, spread your study sessions over time.\n\n## Optimal Review Schedule\n- **Day 1:** Learn new material\n- **Day 2:** First review\n- **Day 4:** Second review\n- **Day 7:** Third review\n- **Day 14:** Fourth review\n\n## Tools That Help\n- Anki (free flashcard app with built-in spacing)\n- Quizlet (has spaced repetition mode)\n- Paper calendar for planning reviews\n\n**Remember:** Short, frequent sessions beat long cramming sessions every time!",
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
                'recommendation' => 'A coach can help you apply these techniques to your specific classes and learning style.',
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
                'prompts' => [
                    'Which subject will you focus on first?',
                    'When will you have your initial study session?',
                    'Schedule your 4 review sessions (Day 2, 4, 7, 14):',
                    'What active recall techniques will you use?',
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
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Understanding Self-Confidence\n\nSelf-confidence is believing in your ability to handle challenges and learn from mistakes. It's NOT:\n- Being perfect\n- Never feeling afraid\n- Thinking you're better than others\n\n**True confidence** means knowing you have value as a person, regardless of your achievements or what others think.",
            ],
            'estimated_duration_minutes' => 5,
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
                'prompts' => [
                    'On a scale of 1-10, how confident do you feel generally?',
                    'In what situations do you feel most confident?',
                    'When do you feel least confident?',
                    'What negative thoughts do you often have about yourself?',
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
                'body' => "# Reframing Negative Self-Talk\n\n## Common Patterns\n- **All-or-nothing:** \"I failed the test, I'm stupid\"\n- **Mind reading:** \"Everyone thinks I'm weird\"\n- **Fortune telling:** \"I'll definitely mess this up\"\n\n## Reframing Examples\n- \"I'm stupid\" → \"I'm still learning this subject\"\n- \"Everyone hates me\" → \"Some people like me, and that's what matters\"\n- \"I can't do anything right\" → \"I'm good at some things and learning others\"",
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
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# Anger: A Normal Emotion\n\nAnger is a natural response to perceived threats, injustice, or frustration. It's not bad to feel angry - it's what we DO with anger that matters.\n\n## The Anger Cycle\n1. **Trigger** - Something happens\n2. **Thoughts** - We interpret the situation\n3. **Physical response** - Body prepares for action\n4. **Behavior** - How we express the anger\n\nUnderstanding this cycle gives us points where we can intervene.",
            ],
            'estimated_duration_minutes' => 5,
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
                'prompts' => [
                    'Describe a recent situation that made you angry:',
                    'What physical signs did you notice in your body?',
                    'How did you respond? Was it helpful or unhelpful?',
                    'What do you wish you had done differently?',
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
            'instructions' => 'When you feel anger building, use your senses to ground yourself:\n\n- Name 5 things you can SEE\n- Name 4 things you can TOUCH\n- Name 3 things you can HEAR\n- Name 2 things you can SMELL\n- Name 1 thing you can TASTE\n\nPractice this now, even when calm, so it becomes automatic.',
            'content_type' => MiniCourseStep::CONTENT_INTERACTIVE,
            'content_data' => [
                'activity_type' => 'grounding_exercise',
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
            'instructions' => 'If anger is frequently affecting your relationships or wellགbeing, talking to a therapist can help develop personalized strategies.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'note' => 'This step is optional but recommended if anger is a recurring challenge.',
            ],
            'provider_id' => $therapist?->id,
            'estimated_duration_minutes' => 2,
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
                'prompts' => [
                    'My early warning signs of anger are:',
                    'When I notice anger building, I will:',
                    'If I\'m too angry to think clearly, I will:',
                    'After I calm down, I will:',
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
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'body' => "# SMART Goals\n\n**S** - Specific: Clear and well-defined\n**M** - Measurable: You can track progress\n**A** - Achievable: Challenging but realistic\n**R** - Relevant: Matters to YOU\n**T** - Time-bound: Has a deadline\n\n## Example\n❌ \"I want to get better grades\"\n✅ \"I will raise my math grade from B to A by the end of the semester by studying 30 minutes daily and attending tutoring once a week\"",
            ],
            'estimated_duration_minutes' => 5,
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
                'prompts' => [
                    'What do you want your life to look like in 1 year?',
                    'What\'s one thing you wish was different about school?',
                    'What skill would make the biggest difference in your life?',
                    'What would make you proud of yourself?',
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
            'instructions' => 'Using the SMART framework, write out a complete goal.',
            'content_type' => MiniCourseStep::CONTENT_TEXT,
            'content_data' => [
                'prompts' => [
                    'Specific - What exactly do you want to achieve?',
                    'Measurable - How will you know you\'ve succeeded?',
                    'Achievable - What steps will you take?',
                    'Relevant - Why does this matter to you?',
                    'Time-bound - By when will you achieve this?',
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

        foreach ($students as $index => $student) {
            // Enroll some students in courses with varying progress
            $course = $courses[$index % count($courses)];
            if ($course->status !== MiniCourse::STATUS_ACTIVE) {
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

        foreach ($highRiskStudents as $student) {
            $course = $courses[array_rand($courses)];
            if ($course->status !== MiniCourse::STATUS_ACTIVE) {
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
