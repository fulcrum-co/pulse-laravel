<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ContactEnhancedSeeder extends Seeder
{
    /**
     * Create 50 demo contacts with proper risk distribution.
     * Risk distribution: 20% good (10), 50% low (25), 30% high (15)
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $counselor = User::where('primary_role', 'admin')
            ->where('org_id', $school->id)
            ->first();

        // Generate 50 students with proper distribution (using firstOrCreate for idempotency)
        $riskCounts = [
            'good' => 10,  // 20%
            'low' => 25,   // 50%
            'high' => 15,  // 30%
        ];

        $allRisks = [];
        foreach ($riskCounts as $level => $count) {
            $allRisks = array_merge($allRisks, array_fill(0, $count, $level));
        }
        shuffle($allRisks); // Randomize order

        $firstNames = [
            'male' => ['Alex', 'Liam', 'Noah', 'Ethan', 'Mason', 'Lucas', 'Jacob', 'William', 'Benjamin', 'James', 'Henry', 'Alexander', 'Daniel', 'Michael', 'Logan', 'Jackson', 'Sebastian', 'Owen', 'Carter', 'Wyatt', 'Luke', 'Gabriel', 'Isaac', 'Caleb', 'Nathan'],
            'female' => ['Emma', 'Olivia', 'Ava', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Amelia', 'Harper', 'Evelyn', 'Luna', 'Chloe', 'Ella', 'Grace', 'Lily', 'Madison', 'Zoey', 'Layla', 'Riley', 'Nora', 'Hannah', 'Aria', 'Scarlett', 'Victoria', 'Penelope'],
        ];

        $lastNames = ['Johnson', 'Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green', 'Baker', 'Adams', 'Nelson', 'Hill', 'Ramirez', 'Campbell', 'Mitchell', 'Roberts', 'Carter', 'Phillips', 'Evans', 'Turner', 'Torres', 'Parker', 'Collins', 'Edwards', 'Stewart', 'Morris', 'Rogers', 'Reed', 'Cook', 'Morgan'];

        $grades = ['9', '10', '11', '12'];
        $genders = ['male', 'female'];
        $ethnicities = ['Caucasian', 'African American', 'Hispanic', 'Asian', 'Native American', 'Pacific Islander', 'Multiracial'];

        foreach ($allRisks as $index => $riskLevel) {
            $gender = $genders[array_rand($genders)];
            $firstName = $firstNames[$gender][$index % count($firstNames[$gender])];
            $lastName = $lastNames[$index % count($lastNames)];
            $email = strtolower($firstName[0] . $lastName) . ($index + 1) . '@student.lincolnhigh.edu';

            // Generate risk score based on level
            $riskScore = match ($riskLevel) {
                'good' => rand(0, 30) / 10,      // 0.0 - 3.0
                'low' => rand(31, 60) / 10,      // 3.1 - 6.0
                'high' => rand(61, 100) / 10,    // 6.1 - 10.0
            };

            // Create user account (idempotent - won't duplicate if exists)
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'org_id' => $school->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => Hash::make('password'),
                    'primary_role' => 'student',
                    'avatar_url' => 'https://randomuser.me/api/portraits/' . ($gender === 'male' ? 'men' : 'women') . '/' . (($index % 50) + 1) . '.jpg',
                    'active' => true,
                ]
            );

            // Create student record (idempotent - skip if user already has student record for this org)
            Student::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'org_id' => $school->id,
                ],
                [
                    'student_number' => 'STU' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                    'grade_level' => $grades[array_rand($grades)],
                    'date_of_birth' => now()->subYears(rand(14, 18))->subDays(rand(0, 365)),
                    'gender' => $gender,
                    'ethnicity' => $ethnicities[array_rand($ethnicities)],
                    'iep_status' => $this->weightedRandom(['yes' => 15, 'no' => 85]) === 'yes',
                    'ell_status' => $this->weightedRandom(['yes' => 10, 'no' => 90]) === 'yes',
                    'free_reduced_lunch' => $this->weightedRandom(['yes' => 40, 'no' => 60]) === 'yes',
                    'enrollment_status' => 'active',
                    'enrollment_date' => now()->subMonths(rand(1, 48)),
                    'risk_level' => $riskLevel,
                    'risk_score' => $riskScore,
                    'tags' => $this->generateTags($riskLevel),
                    'counselor_user_id' => $counselor?->id,
                ]
            );
        }

        $this->command->info('Created 50 demo contacts with proper risk distribution:');
        $this->command->info('  - Good: 10 contacts (20%)');
        $this->command->info('  - Low Risk: 25 contacts (50%)');
        $this->command->info('  - High Risk: 15 contacts (30%)');
    }

    /**
     * Generate tags based on risk level.
     */
    private function generateTags(string $riskLevel): array
    {
        $allTags = [
            'good' => [
                'Honor Roll',
                'Athletics',
                'Student Council',
                'Drama Club',
                'Music',
                'National Honor Society',
                'Debate Team',
                'Yearbook',
                'AP Scholar',
            ],
            'low' => [
                'Tutoring',
                'Study Group',
                'Mentorship',
                'After School Program',
                'Progress Monitoring',
            ],
            'high' => [
                'Priority Support',
                'Weekly Check-in',
                'Parent Contact',
                'Intervention Plan',
                'Attendance Monitor',
                'Behavior Support',
                'Counseling Services',
            ],
        ];

        $tags = [];
        $possibleTags = $allTags[$riskLevel];
        $numTags = match ($riskLevel) {
            'good' => rand(1, 3),    // Good students have more activities
            'low' => rand(0, 2),     // Some support tags
            'high' => rand(1, 3),    // Multiple intervention tags
        };

        $numTags = min($numTags, count($possibleTags));

        // Return empty array if no tags to select
        if ($numTags === 0 || empty($possibleTags)) {
            return [];
        }

        $selectedTags = (array) array_rand(array_flip($possibleTags), $numTags);

        return $selectedTags;
    }

    /**
     * Return a weighted random value.
     */
    private function weightedRandom(array $weights): string
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
