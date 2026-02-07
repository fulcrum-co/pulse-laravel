<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\DistributionDelivery;
use App\Models\DistributionRecipient;
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
        $totalRecipients = 0;

        foreach ($distributions as $distribution) {
            $numRecipients = rand(5, 10);
            $recipients = $students->random(min($numRecipients, $students->count()));

            // Create a delivery batch for this distribution
            $delivery = DistributionDelivery::create([
                'distribution_id' => $distribution->id,
                'status' => 'completed',
                'total_recipients' => $recipients->count(),
                'sent_count' => $recipients->count(),
                'failed_count' => 0,
                'started_at' => now()->subDays(rand(1, 60)),
                'completed_at' => now()->subDays(rand(0, 59)),
            ]);

            foreach ($recipients as $student) {
                // Weighted random: 90% delivered, 8% sent, 2% failed
                $rand = rand(1, 100);
                $status = $rand <= 90 ? 'delivered' : ($rand <= 98 ? 'sent' : 'failed');

                DistributionRecipient::create([
                    'delivery_id' => $delivery->id,
                    'contact_type' => 'App\\Models\\Student',
                    'contact_id' => $student->id,
                    'status' => $status,
                    'sent_at' => $delivery->started_at,
                    'delivered_at' => $status === 'delivered' ? $delivery->completed_at : null,
                ]);
                $totalRecipients++;
            }
        }

        $this->command->info("Created {$distributions->count()} distributions with {$totalRecipients} recipients");
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

        return collect($distributionDefs)->map(function($d) use ($orgId, $userId) {
            $isRecurring = !in_array($d['frequency'], ['one_time']);
            $recurrenceConfig = null;

            if ($isRecurring) {
                $recurrenceConfig = [
                    'type' => $d['frequency'], // monthly, weekly, quarterly
                    'interval' => 1,
                    'days' => $d['frequency'] === 'weekly' ? ['monday'] : null,
                ];
            }

            return Distribution::create([
                'org_id' => $orgId,
                'title' => $d['title'],
                'description' => $d['desc'],
                'distribution_type' => $isRecurring ? 'recurring' : 'one_time',
                'content_type' => $d['type'] === 'report' ? 'report' : 'custom',
                'channel' => 'email',
                'status' => 'active',
                'recurrence_config' => $recurrenceConfig,
                'created_by' => $userId,
                'created_at' => now()->subDays(rand(10, 90)),
            ]);
        });
    }
}
