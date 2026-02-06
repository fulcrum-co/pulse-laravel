<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveyEnhancedSeeder extends Seeder
{
    /**
     * Create 6-8 surveys with 150-200 attempts (3-5 per student).
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();
        $students = Student::where('org_id', $school->id)->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found! Run ContactEnhancedSeeder first.');
            return;
        }

        // Create 7 comprehensive surveys
        $surveys = $this->createSurveys($school->id, $admin->id);

        // Create 150-200 attempts (3-4 per student on average)
        $totalAttempts = 0;
        $startDate = now()->subDays(90);

        foreach ($students as $student) {
            $numAttempts = rand(3, 5); // 3-5 attempts per student
            $availableSurveys = $surveys->shuffle();

            for ($i = 0; $i < $numAttempts && $i < count($availableSurveys); $i++) {
                $survey = $availableSurveys[$i];
                $completedAt = $startDate->copy()->addDays(rand(0, 90))->addHours(rand(8, 16));

                $this->createSurveyAttempt($survey, $student, $completedAt);
                $totalAttempts++;
            }
        }

        $this->command->info("Created {$surveys->count()} surveys with {$totalAttempts} attempts");
    }

    private function createSurveys(int $orgId, int $adminId): \Illuminate\Support\Collection
    {
        $surveys = collect();

        // Survey 1: Wellness Check-In
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Weekly Wellness Check-In',
            'description' => 'Quick check on student wellbeing and mental health',
            'survey_type' => 'wellness',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'How are you feeling overall today?', 'min' => 1, 'max' => 5, 'labels' => ['Very Bad', 'Bad', 'Okay', 'Good', 'Great']],
                ['id' => 'q2', 'type' => 'scale', 'question' => 'How well did you sleep last night?', 'min' => 1, 'max' => 5, 'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well']],
                ['id' => 'q3', 'type' => 'scale', 'question' => 'How stressed do you feel?', 'min' => 1, 'max' => 5, 'labels' => ['Extremely', 'Very', 'Moderately', 'A Little', 'Not At All']],
                ['id' => 'q4', 'type' => 'text', 'question' => 'Anything you'd like to share?', 'optional' => true],
            ],
            'status' => 'active',
            'is_anonymous' => false,
            'estimated_duration_minutes' => 5,
            'start_date' => now()->subWeeks(12),
            'target_grades' => ['9', '10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 2: Academic Engagement
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Student Engagement Survey',
            'description' => 'Understanding student engagement with coursework and school activities',
            'survey_type' => 'academic',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'How engaged do you feel in your classes?', 'min' => 1, 'max' => 5, 'labels' => ['Not Engaged', 'Slightly', 'Moderately', 'Engaged', 'Highly Engaged']],
                ['id' => 'q2', 'type' => 'multiple_choice', 'question' => 'How many hours per week do you study outside of class?', 'options' => ['Less than 2', '2-5', '5-10', '10-15', 'More than 15']],
                ['id' => 'q3', 'type' => 'scale', 'question' => 'Do you feel challenged by your coursework?', 'min' => 1, 'max' => 5, 'labels' => ['Too Easy', 'Somewhat Easy', 'Just Right', 'Challenging', 'Too Difficult']],
            ],
            'status' => 'active',
            'is_anonymous' => false,
            'estimated_duration_minutes' => 8,
            'start_date' => now()->subWeeks(10),
            'target_grades' => ['9', '10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 3: Social-Emotional Learning
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Social-Emotional Check-In',
            'description' => 'Assessing social connections and emotional wellbeing',
            'survey_type' => 'sel',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'How connected do you feel to your peers?', 'min' => 1, 'max' => 5, 'labels' => ['Very Isolated', 'Isolated', 'Neutral', 'Connected', 'Very Connected']],
                ['id' => 'q2', 'type' => 'scale', 'question' => 'Can you identify and manage your emotions?', 'min' => 1, 'max' => 5, 'labels' => ['Never', 'Rarely', 'Sometimes', 'Often', 'Always']],
                ['id' => 'q3', 'type' => 'scale', 'question' => 'Do you feel safe at school?', 'min' => 1, 'max' => 5, 'labels' => ['Not Safe', 'Unsafe', 'Neutral', 'Safe', 'Very Safe']],
            ],
            'status' => 'active',
            'is_anonymous' => true,
            'estimated_duration_minutes' => 7,
            'start_date' => now()->subWeeks(8),
            'target_grades' => ['9', '10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 4: Goal Setting & Progress
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Goal Progress Assessment',
            'description' => 'Check-in on personal and academic goals',
            'survey_type' => 'goal_tracking',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'How much progress have you made on your goals?', 'min' => 1, 'max' => 5, 'labels' => ['No Progress', 'Little', 'Some', 'Good Progress', 'Excellent Progress']],
                ['id' => 'q2', 'type' => 'text', 'question' => 'What is one goal you are proud of achieving?', 'optional' => false],
                ['id' => 'q3', 'type' => 'text', 'question' => 'What obstacles are preventing you from reaching your goals?', 'optional' => true],
            ],
            'status' => 'active',
            'is_anonymous' => false,
            'estimated_duration_minutes' => 10,
            'start_date' => now()->subWeeks(6),
            'target_grades' => ['10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 5: Attendance Barriers
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Attendance Barriers Survey',
            'description' => 'Understanding factors affecting student attendance',
            'survey_type' => 'attendance',
            'questions' => [
                ['id' => 'q1', 'type' => 'multiple_choice', 'question' => 'What prevents you from attending school regularly?', 'options' => ['Transportation', 'Health Issues', 'Family Responsibilities', 'School Climate', 'Nothing', 'Other']],
                ['id' => 'q2', 'type' => 'scale', 'question' => 'How important is attendance to you?', 'min' => 1, 'max' => 5, 'labels' => ['Not Important', 'Slightly', 'Moderately', 'Important', 'Very Important']],
            ],
            'status' => 'active',
            'is_anonymous' => true,
            'estimated_duration_minutes' => 5,
            'start_date' => now()->subWeeks(4),
            'target_grades' => ['9', '10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 6: Teacher Support
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Teacher & Support Services Feedback',
            'description' => 'Student feedback on quality of instruction and support',
            'survey_type' => 'feedback',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'My teachers care about my success', 'min' => 1, 'max' => 5, 'labels' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']],
                ['id' => 'q2', 'type' => 'scale', 'question' => 'I can get help when I need it', 'min' => 1, 'max' => 5, 'labels' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']],
                ['id' => 'q3', 'type' => 'text', 'question' => 'What additional support would be helpful?', 'optional' => true],
            ],
            'status' => 'active',
            'is_anonymous' => true,
            'estimated_duration_minutes' => 6,
            'start_date' => now()->subWeeks(3),
            'target_grades' => ['9', '10', '11', '12'],
            'created_by' => $adminId,
        ]));

        // Survey 7: Life Skills Assessment
        $surveys->push(Survey::create([
            'org_id' => $orgId,
            'title' => 'Life Skills Self-Assessment',
            'description' => 'Student self-assessment of important life skills',
            'survey_type' => 'life_skills',
            'questions' => [
                ['id' => 'q1', 'type' => 'scale', 'question' => 'I can manage my time effectively', 'min' => 1, 'max' => 5, 'labels' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']],
                ['id' => 'q2', 'type' => 'scale', 'question' => 'I can communicate my needs clearly', 'min' => 1, 'max' => 5, 'labels' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']],
                ['id' => 'q3', 'type' => 'scale', 'question' => 'I can solve problems independently', 'min' => 1, 'max' => 5, 'labels' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']],
            ],
            'status' => 'active',
            'is_anonymous' => false,
            'estimated_duration_minutes' => 7,
            'start_date' => now()->subWeeks(2),
            'target_grades' => ['11', '12'],
            'created_by' => $adminId,
        ]));

        return $surveys;
    }

    private function createSurveyAttempt(Survey $survey, Student $student, $completedAt): void
    {
        // Generate responses based on student risk level
        $responses = [];
        $totalScore = 0;
        $questionCount = 0;

        foreach ($survey->questions as $question) {
            if ($question['type'] === 'scale') {
                // Higher risk = lower scores
                $score = match ($student->risk_level) {
                    'good' => rand(4, 5),
                    'low' => rand(2, 4),
                    'high' => rand(1, 3),
                };
                $responses[$question['id']] = $score;
                $totalScore += $score;
                $questionCount++;
            } elseif ($question['type'] === 'multiple_choice') {
                $responses[$question['id']] = $question['options'][array_rand($question['options'])];
            } elseif ($question['type'] === 'text') {
                $responses[$question['id']] = ($question['optional'] ?? false) && rand(1, 100) > 60 ? null : 'Sample response text';
            }
        }

        $overallScore = $questionCount > 0 ? ($totalScore / $questionCount) * 20 : null; // Convert to 0-100 scale

        SurveyAttempt::create([
            'survey_id' => $survey->id,
            'student_id' => $student->id,
            'user_id' => $student->user_id,
            'status' => 'completed',
            'responses' => $responses,
            'overall_score' => $overallScore,
            'risk_level' => $student->risk_level,
            'started_at' => $completedAt->copy()->subMinutes(rand(3, 15)),
            'completed_at' => $completedAt,
            'duration_seconds' => rand(180, 900),
            'created_at' => $completedAt,
            'updated_at' => $completedAt,
        ]);
    }
}
