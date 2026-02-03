<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Participant;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('org_type', 'organization')->first();
        $admin = User::where('primary_role', 'admin')->where('org_id', $organization->id)->first();

        // Create wellness check-in survey
        $wellnessSurvey = Survey::create([
            'org_id' => $organization->id,
            'title' => 'Weekly Wellness Check-In',
            'description' => 'A quick check-in to see how participants are feeling this week.',
            'survey_type' => 'wellness',
            'questions' => [
                [
                    'id' => 'q1',
                    'type' => 'scale',
                    'question' => 'How are you feeling overall today?',
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Bad', 'Bad', 'Okay', 'Good', 'Great'],
                ],
                [
                    'id' => 'q2',
                    'type' => 'scale',
                    'question' => 'How well did you sleep last night?',
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Poorly', 'Poorly', 'Okay', 'Well', 'Very Well'],
                ],
                [
                    'id' => 'q3',
                    'type' => 'scale',
                    'question' => 'How stressed do you feel about organization?',
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Very Stressed', 'Stressed', 'Somewhat', 'Relaxed', 'Very Relaxed'],
                ],
                [
                    'id' => 'q4',
                    'type' => 'text',
                    'question' => 'Is there anything you\'d like to share with us?',
                    'optional' => true,
                ],
            ],
            'status' => 'active',
            'is_anonymous' => false,
            'estimated_duration_minutes' => 5,
            'start_date' => now()->subWeeks(2),
            'target_levels' => ['9', '10', '11', '12'],
            'created_by' => $admin->id,
        ]);

        // Create academic stress survey
        Survey::create([
            'org_id' => $organization->id,
            'title' => 'Academic Stress Assessment',
            'description' => 'Understanding how academic pressures affect participant wellbeing.',
            'survey_type' => 'wellness',
            'questions' => [
                [
                    'id' => 'q1',
                    'type' => 'multiple_choice',
                    'question' => 'How many hours per night do you spend on homework?',
                    'options' => ['Less than 1 hour', '1-2 hours', '2-3 hours', '3-4 hours', 'More than 4 hours'],
                ],
                [
                    'id' => 'q2',
                    'type' => 'scale',
                    'question' => 'How confident do you feel about your academic performance?',
                    'min' => 1,
                    'max' => 5,
                    'labels' => ['Not Confident', 'Slightly', 'Moderately', 'Confident', 'Very Confident'],
                ],
            ],
            'status' => 'draft',
            'is_anonymous' => true,
            'estimated_duration_minutes' => 10,
            'target_levels' => ['11', '12'],
            'created_by' => $admin->id,
        ]);

        // Create some survey attempts for the wellness survey
        $participants = Participant::where('org_id', $organization->id)->take(15)->get();

        foreach ($participants as $participant) {
            $overallScore = match ($participant->risk_level) {
                'good' => rand(70, 100) / 10,
                'low' => rand(40, 70) / 10,
                'high' => rand(20, 50) / 10,
            };

            SurveyAttempt::create([
                'survey_id' => $wellnessSurvey->id,
                'participant_id' => $participant->id,
                'user_id' => $participant->user_id,
                'status' => 'completed',
                'responses' => [
                    'q1' => rand(1, 5),
                    'q2' => rand(1, 5),
                    'q3' => rand(1, 5),
                    'q4' => null,
                ],
                'overall_score' => $overallScore,
                'risk_level' => $participant->risk_level,
                'started_at' => now()->subDays(rand(1, 14)),
                'completed_at' => now()->subDays(rand(0, 13)),
                'duration_seconds' => rand(120, 600),
            ]);
        }
    }
}
