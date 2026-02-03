<?php

namespace Database\Seeders;

use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // =====================================
            // WELLNESS QUESTIONS
            // =====================================
            [
                'category' => 'wellness',
                'subcategory' => 'general',
                'question_text' => 'How are you feeling overall today?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Bad', 'Bad', 'Okay', 'Good', 'Great'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'moderate_concern' => ['min' => 3, 'max' => 3],
                    'low_concern' => ['min' => 4],
                    'ai_prompt' => 'Scores of 1-2 indicate the participant may need immediate support. Consider follow-up with support_person.',
                ],
                'tags' => ['daily-check', 'mood', 'general-wellness'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'sleep',
                'question_text' => 'How well did you sleep last night?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Poor sleep patterns (1-2) over multiple check-ins may indicate stress or other issues requiring support.',
                ],
                'tags' => ['sleep', 'health', 'daily-check'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'stress',
                'question_text' => 'How stressed do you feel right now?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Not at All', 'A Little', 'Somewhat', 'Very', 'Extremely'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['min' => 4],
                    'moderate_concern' => ['min' => 3, 'max' => 3],
                    'ai_prompt' => 'High stress levels (4-5) should trigger check-in. Look for patterns in timing (exams, assignments).',
                ],
                'tags' => ['stress', 'anxiety', 'mental-health'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'anxiety',
                'question_text' => 'Over the past week, how often have you felt nervous, anxious, or on edge?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Not at all', 'Several days', 'More than half the days', 'Nearly every day'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['More than half the days', 'Nearly every day']],
                    'ai_prompt' => 'Frequent anxiety (more than half days) may indicate need for counseling referral.',
                ],
                'tags' => ['anxiety', 'mental-health', 'weekly-check'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'mood',
                'question_text' => 'Over the past week, how often have you felt down, depressed, or hopeless?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Not at all', 'Several days', 'More than half the days', 'Nearly every day'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['More than half the days', 'Nearly every day']],
                    'ai_prompt' => 'Persistent low mood requires immediate attention and potential mental health referral.',
                ],
                'tags' => ['depression', 'mood', 'mental-health', 'weekly-check'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'support',
                'question_text' => 'Do you have someone you can talk to when you\'re feeling upset or stressed?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Yes, always', 'Yes, sometimes', 'Rarely', 'No, never'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['Rarely', 'No, never']],
                    'ai_prompt' => 'Lack of support system is a risk factor. Consider connecting participant with peer support or support_person.',
                ],
                'tags' => ['support', 'social', 'mental-health'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'safety',
                'question_text' => 'Do you feel safe at organization?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Never', 'Rarely', 'Sometimes', 'Usually', 'Always'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Safety concerns require immediate attention. Follow up to understand specific issues.',
                ],
                'tags' => ['safety', 'organization-climate', 'bullying'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'energy',
                'question_text' => 'How would you rate your energy level today?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Low', 'Low', 'Moderate', 'High', 'Very High'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Consistently low energy may indicate health issues, poor sleep, or depression.',
                ],
                'tags' => ['energy', 'health', 'daily-check'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'wellness',
                'subcategory' => 'open',
                'question_text' => 'Is there anything you\'d like to share about how you\'re feeling?',
                'question_type' => 'text',
                'options' => null,
                'interpretation_rules' => [
                    'ai_prompt' => 'Analyze for keywords indicating distress, safety concerns, or need for support. Flag mentions of self-harm, bullying, or crisis.',
                ],
                'tags' => ['open-ended', 'feelings', 'qualitative'],
                'is_public' => true,
                'is_validated' => true,
            ],

            // =====================================
            // ACADEMIC QUESTIONS
            // =====================================
            [
                'category' => 'academic',
                'subcategory' => 'workload',
                'question_text' => 'How manageable is your current homework/assignment load?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Overwhelming', 'Heavy', 'Manageable', 'Light', 'Very Light'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Heavy workload (1-2) may indicate need for time management support or accommodation review.',
                ],
                'tags' => ['workload', 'homework', 'time-management'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'academic',
                'subcategory' => 'confidence',
                'question_text' => 'How confident do you feel about your academic performance?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Not at All', 'Slightly', 'Somewhat', 'Confident', 'Very Confident'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Low academic confidence may benefit from tutoring, study skills support, or mentoring.',
                ],
                'tags' => ['confidence', 'self-efficacy', 'academic'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'academic',
                'subcategory' => 'understanding',
                'question_text' => 'Do you understand the material being taught in your classes?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Almost Never', 'Rarely', 'Sometimes', 'Usually', 'Almost Always'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Difficulty understanding material may indicate need for additional support or different instruction approach.',
                ],
                'tags' => ['comprehension', 'learning', 'academic'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'academic',
                'subcategory' => 'participation',
                'question_text' => 'How comfortable are you participating in class discussions?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Uncomfortable', 'Uncomfortable', 'Neutral', 'Comfortable', 'Very Comfortable'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Discomfort with participation may indicate anxiety, language barriers, or learning_group climate issues.',
                ],
                'tags' => ['participation', 'engagement', 'learning_group'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'academic',
                'subcategory' => 'help-seeking',
                'question_text' => 'When you\'re struggling with organizationwork, do you ask for help?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Always', 'Usually', 'Sometimes', 'Rarely', 'Never'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['Rarely', 'Never']],
                    'ai_prompt' => 'Reluctance to seek help may indicate fear of judgment or lack of trusted support figures.',
                ],
                'tags' => ['help-seeking', 'support', 'academic'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'academic',
                'subcategory' => 'study-hours',
                'question_text' => 'How many hours per day do you typically spend on homework?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Less than 1 hour', '1-2 hours', '2-3 hours', '3-4 hours', 'More than 4 hours'],
                ],
                'interpretation_rules' => [
                    'ai_prompt' => 'Compare with level level expectations. Excessive homework time (>4 hours) may indicate difficulty or overload.',
                ],
                'tags' => ['homework', 'time', 'workload'],
                'is_public' => true,
                'is_validated' => true,
            ],

            // =====================================
            // SEL (Social-Emotional Learning) QUESTIONS
            // =====================================
            [
                'category' => 'sel',
                'subcategory' => 'relationships',
                'question_text' => 'How would you describe your relationships with other participants?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Poor', 'Poor', 'Okay', 'Good', 'Excellent'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Poor peer relationships may indicate social skills needs, bullying, or isolation.',
                ],
                'tags' => ['relationships', 'social', 'peers'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'sel',
                'subcategory' => 'belonging',
                'question_text' => 'Do you feel like you belong at this organization?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Not at All', 'A Little', 'Somewhat', 'Mostly', 'Completely'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Low sense of belonging is associated with disengagement and dropout risk.',
                ],
                'tags' => ['belonging', 'organization-climate', 'engagement'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'sel',
                'subcategory' => 'emotions',
                'question_text' => 'How well can you manage your emotions when you\'re upset?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Difficulty with emotional regulation may benefit from SEL interventions or counseling.',
                ],
                'tags' => ['emotional-regulation', 'self-management', 'sel'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'sel',
                'subcategory' => 'empathy',
                'question_text' => 'When someone is having a hard time, how often do you try to understand how they feel?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Always'],
                ],
                'interpretation_rules' => [
                    'ai_prompt' => 'Assess empathy development. Low empathy combined with behavioral issues may need targeted intervention.',
                ],
                'tags' => ['empathy', 'social-awareness', 'sel'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'sel',
                'subcategory' => 'goals',
                'question_text' => 'Do you have goals for your future (career, education, personal)?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Yes, clear goals', 'Yes, some ideas', 'Not sure yet', 'No, haven\'t thought about it'],
                ],
                'interpretation_rules' => [
                    'ai_prompt' => 'Lack of future orientation may indicate need for career counseling or mentorship.',
                ],
                'tags' => ['goals', 'future', 'motivation'],
                'is_public' => true,
                'is_validated' => true,
            ],

            // =====================================
            // BEHAVIORAL QUESTIONS
            // =====================================
            [
                'category' => 'behavioral',
                'subcategory' => 'attendance',
                'question_text' => 'How do you feel about coming to organization each day?',
                'question_type' => 'scale',
                'options' => [
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Really Don\'t Want To', 'Don\'t Want To', 'Neutral', 'Want To', 'Really Want To'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['max' => 2],
                    'ai_prompt' => 'Resistance to attending may indicate anxiety, bullying, academic struggles, or other issues.',
                ],
                'tags' => ['attendance', 'engagement', 'motivation'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'behavioral',
                'subcategory' => 'focus',
                'question_text' => 'How often do you have trouble focusing in class?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Almost Always'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['Often', 'Almost Always']],
                    'ai_prompt' => 'Frequent focus issues may indicate ADHD, anxiety, sleep problems, or engagement issues.',
                ],
                'tags' => ['focus', 'attention', 'learning'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'behavioral',
                'subcategory' => 'conflict',
                'question_text' => 'How often do you get into conflicts or arguments with other participants?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Very Often'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['Often', 'Very Often']],
                    'ai_prompt' => 'Frequent conflicts may indicate need for conflict resolution skills or underlying issues.',
                ],
                'tags' => ['conflict', 'behavior', 'social'],
                'is_public' => true,
                'is_validated' => true,
            ],
            [
                'category' => 'behavioral',
                'subcategory' => 'substances',
                'question_text' => 'In the past month, have you used alcohol, tobacco, or other substances?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'choices' => ['No, never', 'No, not this month', 'Yes, once or twice', 'Yes, several times', 'Yes, frequently'],
                ],
                'interpretation_rules' => [
                    'high_concern' => ['values' => ['Yes, several times', 'Yes, frequently']],
                    'ai_prompt' => 'Any substance use requires attention. Frequent use needs immediate intervention.',
                ],
                'tags' => ['substances', 'health', 'risk-behavior'],
                'is_public' => true,
                'is_validated' => true,
            ],
        ];

        foreach ($questions as $question) {
            QuestionBank::create($question);
        }
    }
}
