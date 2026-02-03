<?php

namespace Database\Seeders;

use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;

class SurveyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // =====================================
            // WEEKLY WELLNESS CHECK-IN
            // =====================================
            [
                'name' => 'Weekly Wellness Check-In',
                'description' => 'A quick 5-question survey to check in on learner wellbeing. Covers mood, sleep, stress, safety, and open feedback. Ideal for weekly homeroom or advisory periods.',
                'template_type' => 'wellness_check',
                'questions' => [
                    [
                        'id' => 'q1',
                        'type' => 'scale',
                        'question' => 'How are you feeling overall today?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Very Bad', 'Bad', 'Okay', 'Good', 'Great'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                            'ai_prompt' => 'Scores 1-2 indicate need for immediate check-in.',
                        ],
                    ],
                    [
                        'id' => 'q2',
                        'type' => 'scale',
                        'question' => 'How well did you sleep last night?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q3',
                        'type' => 'scale',
                        'question' => 'How stressed do you feel right now?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Not at All', 'A Little', 'Somewhat', 'Very', 'Extremely'],
                        'interpretation_rules' => [
                            'high_concern' => ['min' => 4],
                        ],
                    ],
                    [
                        'id' => 'q4',
                        'type' => 'scale',
                        'question' => 'Do you feel safe at organization?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Never', 'Rarely', 'Sometimes', 'Usually', 'Always'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                            'ai_prompt' => 'Safety concerns require immediate follow-up.',
                        ],
                    ],
                    [
                        'id' => 'q5',
                        'type' => 'text',
                        'question' => 'Is there anything you\'d like to share with us?',
                        'optional' => true,
                        'interpretation_rules' => [
                            'ai_prompt' => 'Analyze for keywords indicating distress, safety concerns, or need for support.',
                        ],
                    ],
                ],
                'interpretation_config' => [
                    'scoring_method' => 'average',
                    'risk_thresholds' => [
                        'high' => 2.0,
                        'medium' => 3.0,
                    ],
                    'auto_flag_on' => ['q4_low', 'q1_low', 'keywords'],
                    'keyword_flags' => ['hurt', 'scared', 'bullied', 'alone', 'help'],
                ],
                'delivery_defaults' => [
                    'channels' => ['web'],
                    'frequency' => 'weekly',
                    'day_of_week' => 'monday',
                ],
                'tags' => ['wellness', 'weekly', 'quick-check', 'homeroom'],
                'is_public' => true,
                'is_featured' => true,
                'estimated_duration_minutes' => 3,
            ],

            // =====================================
            // ACADEMIC STRESS ASSESSMENT
            // =====================================
            [
                'name' => 'Academic Stress Assessment',
                'description' => 'Comprehensive assessment of academic-related stress, workload, confidence, and support needs. Helps identify learners who may need academic intervention or support.',
                'template_type' => 'academic_stress',
                'questions' => [
                    [
                        'id' => 'q1',
                        'type' => 'scale',
                        'question' => 'How manageable is your current homework/assignment load?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Overwhelming', 'Heavy', 'Manageable', 'Light', 'Very Light'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q2',
                        'type' => 'scale',
                        'question' => 'How confident do you feel about your academic performance?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Not at All', 'Slightly', 'Somewhat', 'Confident', 'Very Confident'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q3',
                        'type' => 'multiple_choice',
                        'question' => 'How many hours per day do you typically spend on homework?',
                        'options' => ['Less than 1 hour', '1-2 hours', '2-3 hours', '3-4 hours', 'More than 4 hours'],
                    ],
                    [
                        'id' => 'q4',
                        'type' => 'scale',
                        'question' => 'Do you understand the material being taught in your classes?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Almost Never', 'Rarely', 'Sometimes', 'Usually', 'Almost Always'],
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q5',
                        'type' => 'multiple_choice',
                        'question' => 'When you\'re struggling with organizationwork, do you ask for help?',
                        'options' => ['Always', 'Usually', 'Sometimes', 'Rarely', 'Never'],
                        'interpretation_rules' => [
                            'high_concern' => ['values' => ['Rarely', 'Never']],
                        ],
                    ],
                    [
                        'id' => 'q6',
                        'type' => 'scale',
                        'question' => 'How stressed do you feel about upcoming tests or assignments?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Not at All', 'A Little', 'Somewhat', 'Very', 'Extremely'],
                        'interpretation_rules' => [
                            'high_concern' => ['min' => 4],
                        ],
                    ],
                    [
                        'id' => 'q7',
                        'type' => 'text',
                        'question' => 'What subjects or topics are you finding most challenging right now?',
                        'optional' => true,
                    ],
                ],
                'interpretation_config' => [
                    'scoring_method' => 'weighted',
                    'weights' => [
                        'q1' => 1.5,
                        'q2' => 1.5,
                        'q4' => 1.2,
                        'q6' => 1.3,
                    ],
                    'risk_thresholds' => [
                        'high' => 2.5,
                        'medium' => 3.5,
                    ],
                    'resource_triggers' => [
                        'tutoring' => ['q4_low', 'q2_low'],
                        'study_skills' => ['q1_low', 'q3_high_hours'],
                        'counseling' => ['q6_high'],
                    ],
                ],
                'delivery_defaults' => [
                    'channels' => ['web'],
                    'frequency' => 'monthly',
                ],
                'tags' => ['academic', 'stress', 'workload', 'comprehensive'],
                'is_public' => true,
                'is_featured' => true,
                'estimated_duration_minutes' => 7,
            ],

            // =====================================
            // SEL SCREENER
            // =====================================
            [
                'name' => 'Social-Emotional Screener',
                'description' => 'Assesses key social-emotional competencies including self-awareness, social awareness, relationship skills, and responsible decision-making. Based on CASEL framework.',
                'template_type' => 'sel_screener',
                'questions' => [
                    [
                        'id' => 'q1',
                        'type' => 'scale',
                        'question' => 'How well can you manage your emotions when you\'re upset?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well'],
                        'category' => 'self_management',
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q2',
                        'type' => 'scale',
                        'question' => 'How would you describe your relationships with other learners?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Very Poor', 'Poor', 'Okay', 'Good', 'Excellent'],
                        'category' => 'relationship_skills',
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q3',
                        'type' => 'scale',
                        'question' => 'Do you feel like you belong at this organization?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Not at All', 'A Little', 'Somewhat', 'Mostly', 'Completely'],
                        'category' => 'social_awareness',
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q4',
                        'type' => 'multiple_choice',
                        'question' => 'When someone is having a hard time, how often do you try to understand how they feel?',
                        'options' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Always'],
                        'category' => 'social_awareness',
                    ],
                    [
                        'id' => 'q5',
                        'type' => 'scale',
                        'question' => 'Do you have someone you can talk to when you\'re feeling upset or stressed?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['No One', 'Rarely', 'Sometimes', 'Usually', 'Always'],
                        'category' => 'relationship_skills',
                        'interpretation_rules' => [
                            'high_concern' => ['max' => 2],
                        ],
                    ],
                    [
                        'id' => 'q6',
                        'type' => 'multiple_choice',
                        'question' => 'Before making an important decision, do you think about the consequences?',
                        'options' => ['Never', 'Rarely', 'Sometimes', 'Usually', 'Always'],
                        'category' => 'responsible_decision_making',
                    ],
                    [
                        'id' => 'q7',
                        'type' => 'scale',
                        'question' => 'How good are you at setting goals and working toward them?',
                        'min' => 1,
                        'max' => 5,
                        'labels' => ['Not at All', 'A Little', 'Somewhat', 'Good', 'Very Good'],
                        'category' => 'self_management',
                    ],
                    [
                        'id' => 'q8',
                        'type' => 'multiple_choice',
                        'question' => 'How often do you get into conflicts or arguments with other learners?',
                        'options' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Very Often'],
                        'category' => 'relationship_skills',
                        'interpretation_rules' => [
                            'high_concern' => ['values' => ['Often', 'Very Often']],
                        ],
                    ],
                ],
                'interpretation_config' => [
                    'scoring_method' => 'domain',
                    'domains' => [
                        'self_awareness' => ['q1'],
                        'self_management' => ['q1', 'q7'],
                        'social_awareness' => ['q3', 'q4'],
                        'relationship_skills' => ['q2', 'q5', 'q8'],
                        'responsible_decision_making' => ['q6'],
                    ],
                    'risk_thresholds' => [
                        'high' => 2.0,
                        'medium' => 3.0,
                    ],
                    'intervention_triggers' => [
                        'sel_group' => ['relationship_skills_low', 'self_management_low'],
                        'counseling' => ['q5_low', 'q3_low'],
                        'conflict_resolution' => ['q8_high'],
                    ],
                ],
                'delivery_defaults' => [
                    'channels' => ['web'],
                    'frequency' => 'quarterly',
                ],
                'tags' => ['sel', 'social-emotional', 'casel', 'screener', 'comprehensive'],
                'is_public' => true,
                'is_featured' => true,
                'estimated_duration_minutes' => 10,
            ],
        ];

        foreach ($templates as $template) {
            SurveyTemplate::create($template);
        }
    }
}
