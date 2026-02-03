<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Learner;
use App\Models\User;
use Illuminate\Database\Seeder;

class LearnerSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('org_type', 'organization')->first();
        $learnerUsers = User::where('primary_role', 'learner')->where('org_id', $organization->id)->get();
        $counselor = User::where('primary_role', 'admin')->where('org_id', $organization->id)->where('email', 'like', '%rodriguez%')->first();

        $grades = ['9', '10', '11', '12'];
        $genders = ['male', 'female', 'non-binary'];
        $riskLevels = ['good', 'good', 'good', 'good', 'good', 'low', 'low', 'high']; // Weighted distribution

        foreach ($learnerUsers as $index => $user) {
            $riskLevel = $riskLevels[array_rand($riskLevels)];
            $riskScore = match ($riskLevel) {
                'good' => rand(0, 30) / 10,
                'low' => rand(31, 60) / 10,
                'high' => rand(61, 100) / 10,
            };

            Learner::create([
                'user_id' => $user->id,
                'org_id' => $organization->id,
                'learner_number' => 'STU'.str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'grade_level' => $grades[array_rand($grades)],
                'date_of_birth' => now()->subYears(rand(14, 18))->subDays(rand(0, 365)),
                'gender' => $genders[array_rand($genders)],
                'iep_status' => rand(0, 10) < 2, // 20% chance
                'ell_status' => rand(0, 10) < 1, // 10% chance
                'free_reduced_lunch' => rand(0, 10) < 3, // 30% chance
                'enrollment_status' => 'active',
                'enrollment_date' => now()->subMonths(rand(1, 36)),
                'risk_level' => $riskLevel,
                'risk_score' => $riskScore,
                'tags' => $this->generateTags($riskLevel),
                'counselor_user_id' => $counselor?->id,
            ]);
        }
    }

    private function generateTags(string $riskLevel): array
    {
        $allTags = [
            'good' => ['Honor Roll', 'Athletics', 'Learner Council', 'Drama Club', 'Music'],
            'low' => ['Tutoring', 'Study Group', 'Mentorship'],
            'high' => ['Priority Support', 'Weekly Check-in', 'Parent Contact'],
        ];

        $tags = [];
        $possibleTags = $allTags[$riskLevel];
        $numTags = rand(0, min(2, count($possibleTags)));

        for ($i = 0; $i < $numTags; $i++) {
            $tag = $possibleTags[array_rand($possibleTags)];
            if (! in_array($tag, $tags)) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
