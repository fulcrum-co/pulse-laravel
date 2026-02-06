<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationMessage;
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
        $totalMessages = 0;

        foreach ($students as $student) {
            $numConversations = rand(0, 3);

            for ($i = 0; $i < $numConversations; $i++) {
                $staffMember = $staff->random();
                $conversation = Conversation::create([
                    'org_id' => $school->id,
                    'subject' => collect(['Check-in needed', 'Support follow-up', 'Attendance concern', 'Intervention update'])->random(),
                    'context_type' => 'App\\Models\\Student',
                    'context_id' => $student->id,
                    'status' => collect(['active', 'resolved'])->random([40, 60]),
                    'created_by' => $staffMember->id,
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
                $totalConversations++;

                // Add 2-4 messages per conversation
                $numMessages = rand(2, 4);
                for ($j = 0; $j < $numMessages; $j++) {
                    ConversationMessage::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $j % 2 === 0 ? $staffMember->id : $student->user_id,
                        'message' => collect(['Checking in about recent absence', 'Thanks for the update', 'Following up on our plan', 'Progress looks good'])->random(),
                        'created_at' => $conversation->created_at->copy()->addHours($j * 24),
                    ]);
                    $totalMessages++;
                }
            }
        }

        $this->command->info("Created {$totalConversations} conversations with {$totalMessages} messages");
    }
}
