<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }

        $staff = User::where('org_id', $school->id)->whereIn('primary_role', ['admin', 'counselor', 'teacher'])->get();
        $students = Student::where('org_id', $school->id)->where('risk_level', 'high')->take(15)->get();

        if ($students->isEmpty() || $staff->isEmpty()) {
            $this->command->warn('No students or staff found for conversations. Skipping.');
            return;
        }

        $totalConversations = 0;

        foreach ($students as $student) {
            $numConversations = rand(0, 3);

            for ($i = 0; $i < $numConversations; $i++) {
                $staffMember = $staff->random();
                // Weighted random: 40% active, 60% completed
                $status = rand(1, 100) <= 40 ? 'active' : 'completed';
                $startedAt = now()->subDays(rand(1, 60));

                // Generate 2-4 messages as JSON array
                $numMessages = rand(2, 4);
                $messages = [];
                for ($j = 0; $j < $numMessages; $j++) {
                    $messages[] = [
                        'from' => $j % 2 === 0 ? 'staff' : 'student',
                        'user_id' => $j % 2 === 0 ? $staffMember->id : ($student->user_id ?? null),
                        'text' => collect(['Checking in about recent absence', 'Thanks for the update', 'Following up on our plan', 'Progress looks good'])->random(),
                        'timestamp' => $startedAt->copy()->addHours($j * 24)->toISOString(),
                    ];
                }

                Conversation::create([
                    'student_id' => $student->id,
                    'conversation_type' => collect(['check_in', 'follow_up', 'support'])->random(),
                    'status' => $status,
                    'messages' => $messages,
                    'sentiment' => collect(['positive', 'neutral', 'mixed'])->random(),
                    'requires_follow_up' => $status === 'active',
                    'started_at' => $startedAt,
                    'ended_at' => $status === 'completed' ? $startedAt->copy()->addDays(rand(1, 7)) : null,
                ]);
                $totalConversations++;
            }
        }

        $this->command->info("Created {$totalConversations} conversations");
    }
}
