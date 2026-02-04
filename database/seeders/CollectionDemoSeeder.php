<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\CollectionSchedule;
use App\Models\CollectionSession;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CollectionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();

        if (! $org) {
            $org = Organization::create([
                'org_type' => 'organization',
                'org_name' => 'Demo Organization',
                'active' => true,
                'timezone' => 'America/New_York',
            ]);
        }

        $user = User::where('org_id', $org->id)->first();

        if (! $user) {
            $user = User::create([
                'org_id' => $org->id,
                'current_org_id' => $org->id,
                'first_name' => 'Demo',
                'last_name' => 'Admin',
                'email' => 'demo-admin@pulse.local',
                'password' => Hash::make('password'),
                'primary_role' => 'admin',
                'active' => true,
            ]);
        }

        $contacts = Contact::where('org_id', $org->id)->limit(12)->get();

        if ($contacts->isEmpty()) {
            $contacts = collect();

            for ($i = 0; $i < 8; $i++) {
                $contactUser = User::create([
                    'org_id' => $org->id,
                    'current_org_id' => $org->id,
                    'first_name' => 'Contact',
                    'last_name' => (string) ($i + 1),
                    'email' => 'contact'.$i.'@pulse.local',
                    'password' => Hash::make('password'),
                    'primary_role' => 'contact',
                    'active' => true,
                ]);

                $contacts->push(Contact::create([
                    'user_id' => $contactUser->id,
                    'org_id' => $org->id,
                    'student_number' => 'CNT-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'grade_level' => (string) (9 + ($i % 4)),
                    'enrollment_status' => 'active',
                    'risk_level' => $i % 3 === 0 ? 'high' : 'low',
                ]));
            }
        }

        $baseCollections = [
            [
                'title' => 'Weekly Progress Pulse',
                'description' => 'Quick weekly check-in for engagement and progress.',
                'collection_type' => Collection::TYPE_RECURRING,
                'data_source' => Collection::SOURCE_INLINE,
                'format_mode' => Collection::FORMAT_CONVERSATIONAL,
                'status' => Collection::STATUS_ACTIVE,
                'inline_questions' => [
                    ['type' => 'scale', 'prompt' => 'How engaged was this contact this week?', 'min' => 1, 'max' => 5],
                    ['type' => 'text', 'prompt' => 'What support would help most right now?'],
                ],
                'contact_scope' => [
                    'target_type' => 'contacts',
                    'levels' => ['9', '10'],
                ],
            ],
            [
                'title' => 'Monthly Coaching Reflections',
                'description' => 'Gather reflections and feedback from contacts each month.',
                'collection_type' => Collection::TYPE_RECURRING,
                'data_source' => Collection::SOURCE_INLINE,
                'format_mode' => Collection::FORMAT_FORM,
                'status' => Collection::STATUS_PAUSED,
                'inline_questions' => [
                    ['type' => 'text', 'prompt' => 'What went well this month?'],
                    ['type' => 'text', 'prompt' => 'What should improve next month?'],
                ],
                'contact_scope' => [
                    'target_type' => 'contacts',
                    'levels' => ['11', '12'],
                ],
            ],
            [
                'title' => 'Event Follow-Up Survey',
                'description' => 'One-time feedback from participants after an event.',
                'collection_type' => Collection::TYPE_ONE_TIME,
                'data_source' => Collection::SOURCE_INLINE,
                'format_mode' => Collection::FORMAT_GRID,
                'status' => Collection::STATUS_DRAFT,
                'inline_questions' => [
                    ['type' => 'scale', 'prompt' => 'Overall experience score', 'min' => 1, 'max' => 5],
                    ['type' => 'text', 'prompt' => 'Key takeaway'],
                ],
                'contact_scope' => [
                    'target_type' => 'contacts',
                ],
            ],
        ];

        foreach ($baseCollections as $collectionData) {
            $collection = Collection::create(array_merge($collectionData, [
                'org_id' => $org->id,
                'created_by' => $user->id,
                'settings' => [
                    'voice_enabled' => true,
                    'ai_follow_up' => true,
                ],
                'reminder_config' => [
                    'enabled' => true,
                    'channels' => ['email'],
                    'lead_time_minutes' => 60,
                    'follow_up_enabled' => true,
                    'follow_up_delay_hours' => 24,
                ],
            ]));

            if ($collection->collection_type !== Collection::TYPE_ONE_TIME) {
                $schedule = CollectionSchedule::create([
                    'collection_id' => $collection->id,
                    'schedule_type' => CollectionSchedule::TYPE_INTERVAL,
                    'interval_type' => CollectionSchedule::INTERVAL_WEEKLY,
                    'interval_value' => 1,
                    'timezone' => $org->timezone ?? 'America/New_York',
                    'start_date' => now()->subWeek(),
                    'is_active' => $collection->status === Collection::STATUS_ACTIVE,
                    'next_scheduled_at' => now()->addDays(3),
                ]);
            } else {
                $schedule = null;
            }

            $session = CollectionSession::create([
                'collection_id' => $collection->id,
                'schedule_id' => $schedule?->id,
                'session_date' => now()->subDays(2),
                'status' => CollectionSession::STATUS_COMPLETED,
                'total_contacts' => 0,
                'completed_count' => 0,
                'skipped_count' => 0,
                'completion_rate' => 0,
                'started_at' => now()->subDays(2)->addHours(1),
                'completed_at' => now()->subDays(2)->addHours(2),
                'collected_by_user_id' => $user->id,
            ]);

            $sampleContacts = $contacts->shuffle()->take(5);
            $completed = 0;

            foreach ($sampleContacts as $contact) {
                CollectionEntry::create([
                    'collection_id' => $collection->id,
                    'session_id' => $session->id,
                    'contact_type' => Contact::class,
                    'contact_id' => $contact->id,
                    'collected_by_user_id' => $user->id,
                    'status' => CollectionEntry::STATUS_COMPLETED,
                    'input_mode' => $collection->format_mode,
                    'responses' => [
                        'q1' => rand(2, 5),
                        'q2' => 'Sample response for demo purposes.',
                    ],
                    'duration_seconds' => rand(60, 240),
                    'started_at' => $session->started_at,
                    'completed_at' => $session->completed_at,
                ]);

                $completed++;
            }

            $session->update([
                'total_contacts' => $sampleContacts->count(),
                'completed_count' => $completed,
                'completion_rate' => $sampleContacts->count() > 0 ? round(($completed / $sampleContacts->count()) * 100, 2) : 0,
            ]);
        }
    }
}
