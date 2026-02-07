<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\CollectionSession;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class CollectionEnhancedSeeder extends Seeder
{
    /**
     * Create 10 collections with 25-30 sessions and 300-400 entries.
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
            $this->command->error('No students found!');
            return;
        }

        $collections = $this->createCollections($school->id, $admin->id);

        $totalSessions = 0;
        $totalEntries = 0;
        $startDate = now()->subDays(90);

        foreach ($collections as $collection) {
            // Create 2-4 sessions per collection (totaling ~25-30)
            $numSessions = rand(2, 4);

            for ($i = 0; $i < $numSessions; $i++) {
                $sessionDate = $startDate->copy()->addDays(rand(0, 90));

                $session = CollectionSession::create([
                    'collection_id' => $collection->id,
                    'session_date' => $sessionDate,
                    'status' => rand(1, 100) <= 80 ? 'completed' : 'in_progress',
                    'started_at' => $sessionDate,
                    'completed_at' => rand(1, 100) <= 80 ? $sessionDate->copy()->addDays(rand(1, 7)) : null,
                    'collected_by_user_id' => $admin->id,
                    'total_contacts' => 0,
                    'completed_count' => 0,
                    'skipped_count' => 0,
                    'completion_rate' => 0,
                ]);

                $totalSessions++;

                // Create entries for random students (10-15 per session)
                $numEntries = rand(10, 15);
                $selectedStudents = $students->random(min($numEntries, $students->count()));

                foreach ($selectedStudents as $student) {
                    $entryData = $this->generateEntryData($collection->collection_type, $student);

                    CollectionEntry::create([
                        'session_id' => $session->id,
                        'collection_id' => $collection->id,
                        'contact_type' => 'App\\Models\\Student',
                        'contact_id' => $student->id,
                        'status' => $this->weightedRandom([
                            'completed' => 70,
                            'in_progress' => 20,
                            'pending' => 10,
                        ]),
                        'responses' => $entryData,
                        'completed_at' => rand(1, 100) <= 70 ? $sessionDate->copy()->addHours(rand(1, 48)) : null,
                        'collected_by_user_id' => $admin->id,
                        'input_mode' => 'form',
                        'created_at' => $sessionDate,
                        'updated_at' => $sessionDate->copy()->addHours(rand(0, 72)),
                    ]);

                    $totalEntries++;
                }
            }
        }

        $this->command->info("Created {$collections->count()} collections with {$totalSessions} sessions and {$totalEntries} entries");
    }

    private function createCollections(int $orgId, int $adminId): \Illuminate\Support\Collection
    {
        $collections = collect();

        $collectionDefs = [
            ['title' => 'Weekly Attendance Check', 'desc' => 'Weekly tracking of student attendance patterns', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Behavioral Progress Update', 'desc' => 'Monthly behavioral progress tracking', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Academic Performance Snapshot', 'desc' => 'Quarterly academic performance data collection', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Parent Contact Log', 'desc' => 'Documentation of parent communication and engagement', 'type' => Collection::TYPE_ONE_TIME ?? 'one_time'],
            ['title' => 'Intervention Services Documentation', 'desc' => 'Tracking interventions and support services provided', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Student Goal Progress', 'desc' => 'Bi-weekly student goal setting and progress tracking', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Counseling Session Notes', 'desc' => 'Documentation of counseling sessions and outcomes', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Special Education Service Hours', 'desc' => 'Tracking of special education service delivery', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
            ['title' => 'Enrichment Activity Participation', 'desc' => 'Student participation in enrichment programs', 'type' => Collection::TYPE_ONE_TIME ?? 'one_time'],
            ['title' => 'Disciplinary Incident Reports', 'desc' => 'Documentation of behavioral incidents and resolutions', 'type' => Collection::TYPE_RECURRING ?? 'recurring'],
        ];

        foreach ($collectionDefs as $def) {
            $collections->push(Collection::create([
                'org_id' => $orgId,
                'title' => $def['title'],
                'description' => $def['desc'],
                'collection_type' => $def['type'],
                'data_source' => 'inline',
                'format_mode' => 'structured',
                'status' => 'active',
                'created_by' => $adminId,
            ]));
        }

        return $collections;
    }

    private function generateEntryData(string $collectionType, Student $student): array
    {
        // Generate realistic data based on collection type and student risk
        return [
            'attendance_rate' => rand(75, 100),
            'behavior_incidents' => match ($student->risk_level) {
                'good' => rand(0, 1),
                'low' => rand(0, 3),
                'high' => rand(2, 8),
            },
            'academic_grade' => match ($student->risk_level) {
                'good' => ['A', 'A-', 'B+'][array_rand(['A', 'A-', 'B+'])],
                'low' => ['B', 'B-', 'C+'][array_rand(['B', 'B-', 'C+'])],
                'high' => ['C', 'C-', 'D'][array_rand(['C', 'C-', 'D'])],
            },
            'notes' => 'Sample collection entry data',
            'progress_rating' => match ($student->risk_level) {
                'good' => rand(4, 5),
                'low' => rand(2, 4),
                'high' => rand(1, 3),
            },
        ];
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $sum = 0;
        foreach ($weights as $key => $weight) {
            $sum += $weight;
            if ($random <= $sum) return $key;
        }
        return array_key_first($weights);
    }
}
