<?php

namespace Database\Seeders;

use App\Models\ContactNote;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactNotesSeeder extends Seeder
{
    /**
     * Create 100-150 contact notes distributed across students (2-5 notes per contact).
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $students = Student::where('org_id', $school->id)->get();
        if ($students->isEmpty()) {
            $this->command->error('No students found! Run ContactEnhancedSeeder first.');
            return;
        }

        $staffUsers = User::where('org_id', $school->id)
            ->whereIn('primary_role', ['admin', 'counselor', 'teacher'])
            ->get();

        if ($staffUsers->isEmpty()) {
            $staffUsers = collect([User::where('org_id', $school->id)->first()]);
        }

        $startDate = now()->subDays(90); // Spread notes over 90 days
        $totalNotes = 0;

        foreach ($students as $student) {
            $numNotes = $this->weightedRandom([
                2 => 20,  // 20% get 2 notes
                3 => 40,  // 40% get 3 notes
                4 => 30,  // 30% get 4 notes
                5 => 10,  // 10% get 5 notes
            ]);

            for ($i = 0; $i < $numNotes; $i++) {
                $noteType = $this->getNoteTypeForRisk($student->risk_level);
                $author = $staffUsers->random();
                $isVoiceMemo = rand(1, 100) <= 15; // 15% are voice memos

                $createdAt = $startDate->copy()->addDays(rand(0, 90))->addHours(rand(8, 16));

                ContactNote::create([
                    'org_id' => $school->id,
                    'contact_type' => 'App\\Models\\Student',
                    'contact_id' => $student->id,
                    'note_type' => $noteType,
                    'content' => $this->generateNoteContent($noteType, $student),
                    'is_voice_memo' => $isVoiceMemo,
                    'audio_duration_seconds' => $isVoiceMemo ? rand(30, 180) : null,
                    'transcription' => $isVoiceMemo ? $this->generateNoteContent($noteType, $student) : null,
                    'transcription_status' => $isVoiceMemo ? ContactNote::TRANSCRIPTION_COMPLETED : null,
                    'is_private' => rand(1, 100) <= 10, // 10% private
                    'visibility' => $this->weightedRandom([
                        ContactNote::VISIBILITY_ORGANIZATION => 70,
                        ContactNote::VISIBILITY_TEAM => 25,
                        ContactNote::VISIBILITY_PRIVATE => 5,
                    ]),
                    'created_by' => $author->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $totalNotes++;
            }
        }

        $this->command->info("Created {$totalNotes} contact notes across {$students->count()} students");
    }

    /**
     * Get note type weighted by risk level.
     */
    private function getNoteTypeForRisk(string $riskLevel): string
    {
        return match ($riskLevel) {
            'good' => $this->weightedRandom([
                ContactNote::TYPE_GENERAL => 40,
                ContactNote::TYPE_MILESTONE => 35,
                ContactNote::TYPE_FOLLOW_UP => 20,
                ContactNote::TYPE_CONCERN => 5,
            ]),
            'low' => $this->weightedRandom([
                ContactNote::TYPE_GENERAL => 30,
                ContactNote::TYPE_FOLLOW_UP => 35,
                ContactNote::TYPE_CONCERN => 25,
                ContactNote::TYPE_MILESTONE => 10,
            ]),
            'high' => $this->weightedRandom([
                ContactNote::TYPE_CONCERN => 40,
                ContactNote::TYPE_FOLLOW_UP => 35,
                ContactNote::TYPE_GENERAL => 15,
                ContactNote::TYPE_MILESTONE => 10,
            ]),
        };
    }

    /**
     * Generate realistic note content based on type and student risk.
     */
    private function generateNoteContent(string $noteType, Student $student): string
    {
        $firstName = $student->user->first_name ?? 'Student';
        $riskLevel = $student->risk_level;

        $templates = [
            ContactNote::TYPE_GENERAL => [
                "Met with {$firstName} today to discuss general progress. Student is engaged in class and participating well.",
                "{$firstName} attended today's session. We discussed their current course load and upcoming assignments.",
                "Quick check-in with {$firstName}. Everything seems to be going smoothly this week.",
                "Regular meeting with {$firstName}. Reviewed their schedule and discussed any concerns.",
                "{$firstName} stopped by during office hours. We had a good conversation about their goals.",
            ],
            ContactNote::TYPE_FOLLOW_UP => [
                "Following up on our previous conversation with {$firstName}. Need to monitor progress over the next two weeks.",
                "Second check-in with {$firstName} regarding attendance concerns discussed last week.",
                "Followed up on action items from previous meeting with {$firstName}. Some improvement noted.",
                "Touching base with {$firstName} about the support strategies we put in place. Will continue monitoring.",
                "Follow-up meeting with {$firstName}. Reviewing progress on goals we set last month.",
            ],
            ContactNote::TYPE_CONCERN => [
                "Concerned about {$firstName}'s recent attendance pattern. Three absences in the past week. Will contact parents.",
                "Meeting with {$firstName} about declining grades in math. Student seems disengaged. May need intervention.",
                "{$firstName} has been showing signs of increased stress. Discussed coping strategies and available resources.",
                "Behavioral concern: {$firstName} had a conflict with peers today. Mediated the situation and will monitor.",
                "Academic concern: {$firstName} is struggling to complete homework assignments. Discussed time management strategies.",
            ],
            ContactNote::TYPE_MILESTONE => [
                "{$firstName} achieved honor roll this quarter! Excellent academic performance. Celebrated this accomplishment.",
                "Great progress: {$firstName} has improved their attendance from 75% to 95% over the past month.",
                "{$firstName} completed their first successful week with all assignments turned in. Positive momentum building.",
                "Milestone reached: {$firstName} scored in the 90th percentile on their recent assessment. Very proud of their effort.",
                "{$firstName} was recognized for outstanding participation in class this week. Positive behavior trend continuing.",
            ],
        ];

        // Modify templates based on risk level
        if ($riskLevel === 'high' && $noteType === ContactNote::TYPE_CONCERN) {
            $highRiskConcerns = [
                "Urgent: {$firstName} has missed 5 consecutive days. Parent contact attempted. Escalating to intervention team.",
                "{$firstName} is at risk of failing multiple classes. Immediate intervention needed. Meeting with parents scheduled.",
                "Crisis check-in with {$firstName}. Student disclosed significant stressors at home. Connected with counseling services.",
                "Behavioral incident: {$firstName} involved in serious altercation. Meeting with admin and parents required.",
                "Academic alert: {$firstName} failing 3 out of 5 classes. Intensive support plan being developed.",
            ];
            $templates[ContactNote::TYPE_CONCERN] = array_merge($templates[ContactNote::TYPE_CONCERN], $highRiskConcerns);
        }

        if ($riskLevel === 'good' && $noteType === ContactNote::TYPE_MILESTONE) {
            $goodMilestones = [
                "{$firstName} was selected as student of the month! Outstanding leadership and academic excellence.",
                "Exceptional achievement: {$firstName} scored perfect marks on their final project. Setting a great example.",
                "{$firstName} was nominated for the National Honor Society. Well-deserved recognition for consistent excellence.",
                "Great news: {$firstName} received a college scholarship offer! Their hard work is paying off.",
                "{$firstName} led a successful peer tutoring session. Demonstrating strong leadership skills.",
            ];
            $templates[ContactNote::TYPE_MILESTONE] = array_merge($templates[ContactNote::TYPE_MILESTONE], $goodMilestones);
        }

        $typeTemplates = $templates[$noteType] ?? $templates[ContactNote::TYPE_GENERAL];
        return $typeTemplates[array_rand($typeTemplates)];
    }

    /**
     * Return a weighted random value.
     */
    private function weightedRandom(array $weights): string|int
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        $sum = 0;
        foreach ($weights as $key => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
