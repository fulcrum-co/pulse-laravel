<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\DistributionDelivery;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributionEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();
        $students = Student::where('org_id', $school->id)->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found!');
            return;
        }

        $distributions = $this->createDistributions($school->id, $admin->id);
        $totalDeliveries = 0;

        foreach ($distributions as $distribution) {
            $numRecipients = rand(5, 10);
            $recipients = $students->random(min($numRecipients, $students->count()));

            foreach ($recipients as $student) {
                DistributionDelivery::create([
                    'distribution_id' => $distribution->id,
                    'org_id' => $school->id,
                    'recipient_type' => 'App\\Models\\Student',
                    'recipient_id' => $student->id,
                    'delivery_method' => collect(['email', 'sms'])->random(),
                    'status' => collect(['sent', 'delivered', 'failed'])->random([90, 8, 2]),
                    'sent_at' => now()->subDays(rand(1, 60)),
                    'delivered_at' => rand(1, 100) <= 90 ? now()->subDays(rand(0, 59)) : null,
                ]);
                $totalDeliveries++;
            }
        }

        $this->command->info("Created {$distributions->count()} distributions with {$totalDeliveries} deliveries");
    }

    private function createDistributions(int $orgId, int $userId): \Illuminate\Support\Collection
    {
        $distributionDefs = [
            ['title' => 'Monthly Wellness Report', 'desc' => 'Student wellness summary for parents', 'type' => 'report', 'frequency' => 'monthly'],
            ['title' => 'Weekly Progress Update', 'desc' => 'Academic and behavioral updates', 'type' => 'message', 'frequency' => 'weekly'],
            ['title' => 'Attendance Alert', 'desc' => 'Chronic absenteeism notification', 'type' => 'alert', 'frequency' => 'one_time'],
            ['title' => 'Intervention Plan Notification', 'desc' => 'New support plan communication', 'type' => 'message', 'frequency' => 'one_time'],
            ['title' => 'Survey Invitation', 'desc' => 'Parent engagement survey request', 'type' => 'message', 'frequency' => 'one_time'],
            ['title' => 'Quarterly Report Card', 'desc' => 'Academic performance summary', 'type' => 'report', 'frequency' => 'quarterly'],
            ['title' => 'Resource Recommendations', 'desc' => 'Personalized resource suggestions', 'type' => 'message', 'frequency' => 'monthly'],
            ['title' => 'Goal Achievement Update', 'desc' => 'Student goal progress notification', 'type' => 'message', 'frequency' => 'monthly'],
        ];

        return collect($distributionDefs)->map(fn($d) => Distribution::create([
            'org_id' => $orgId, 'title' => $d['title'], 'description' => $d['desc'],
            'distribution_type' => $d['type'], 'frequency' => $d['frequency'],
            'status' => 'active', 'created_by' => $userId,
            'created_at' => now()->subDays(rand(10, 90)),
        ]));
    }
}
