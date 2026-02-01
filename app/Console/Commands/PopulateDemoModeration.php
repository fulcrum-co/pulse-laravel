<?php

namespace App\Console\Commands;

use App\Models\ContentBlock;
use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\ModerationQueueItem;
use Illuminate\Console\Command;

class PopulateDemoModeration extends Command
{
    protected $signature = 'moderation:demo {--count=10 : Number of moderation results to create}';

    protected $description = 'Populate demo moderation data for testing';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $user = auth()->user() ?? \App\Models\User::first();

        if (! $user) {
            $this->error('No users found.');

            return Command::FAILURE;
        }

        $orgId = $user->org_id;
        $this->info("Creating {$count} demo moderation results for org {$orgId}...");

        // Get some courses and content blocks to moderate
        $courses = MiniCourse::where('org_id', $orgId)->limit(5)->get();
        $blocks = ContentBlock::where('org_id', $orgId)->limit(5)->get();

        if ($courses->isEmpty() && $blocks->isEmpty()) {
            $this->warn('No courses or content blocks found. Creating moderation results for hypothetical content...');
        }

        $created = 0;
        $statuses = [
            ContentModerationResult::STATUS_PASSED,
            ContentModerationResult::STATUS_FLAGGED,
            ContentModerationResult::STATUS_FLAGGED,
            ContentModerationResult::STATUS_FLAGGED,
            ContentModerationResult::STATUS_PENDING,
            ContentModerationResult::STATUS_REJECTED,
            ContentModerationResult::STATUS_APPROVED_OVERRIDE,
        ];

        $flagExamples = [
            'Content may contain age-inappropriate vocabulary for younger grades',
            'Health advice should include disclaimer about consulting professionals',
            'Consider adding more diverse examples and perspectives',
            'Some claims need citation or verification',
            'Tone may be too casual for educational context',
            'Content complexity exceeds target grade level',
            'Mental health topic requires sensitivity review',
        ];

        $recommendationExamples = [
            'Add age-appropriate language alternatives',
            'Include professional consultation disclaimer',
            'Expand cultural representation in examples',
            'Add source citations for factual claims',
            'Review and adjust reading level',
            'Have subject matter expert verify accuracy',
        ];

        for ($i = 0; $i < $count; $i++) {
            $status = $statuses[array_rand($statuses)];

            // Generate realistic scores based on status
            $scores = $this->generateScoresForStatus($status);

            // Pick random content to moderate
            $moderatable = null;
            $moderatableType = null;
            $moderatableId = null;

            if ($courses->isNotEmpty() && rand(0, 1)) {
                $moderatable = $courses->random();
                $moderatableType = MiniCourse::class;
                $moderatableId = $moderatable->id;
            } elseif ($blocks->isNotEmpty()) {
                $moderatable = $blocks->random();
                $moderatableType = ContentBlock::class;
                $moderatableId = $moderatable->id;
            } else {
                // Create for hypothetical content
                $moderatableType = rand(0, 1) ? MiniCourse::class : ContentBlock::class;
                $moderatableId = rand(1, 100);
            }

            // Generate flags for non-passed content
            $flags = [];
            $recommendations = [];
            if ($status !== ContentModerationResult::STATUS_PASSED) {
                $numFlags = rand(1, 3);
                $flags = array_slice($flagExamples, 0, $numFlags);
                shuffle($flags);

                $numRecs = rand(1, 2);
                $recommendations = array_slice($recommendationExamples, 0, $numRecs);
                shuffle($recommendations);
            }

            $result = ContentModerationResult::create([
                'org_id' => $orgId,
                'moderatable_type' => $moderatableType,
                'moderatable_id' => $moderatableId,
                'status' => $status,
                'overall_score' => $scores['overall'],
                'age_appropriateness_score' => $scores['age'],
                'clinical_safety_score' => $scores['clinical'],
                'cultural_sensitivity_score' => $scores['cultural'],
                'accuracy_score' => $scores['accuracy'],
                'flags' => $flags,
                'recommendations' => $recommendations,
                'dimension_details' => [
                    'age_appropriateness' => 'Evaluated vocabulary and topic complexity',
                    'clinical_safety' => 'Checked for harmful health/mental health advice',
                    'cultural_sensitivity' => 'Reviewed for inclusive language and representation',
                    'accuracy' => 'Verified factual claims where possible',
                ],
                'human_reviewed' => $status === ContentModerationResult::STATUS_APPROVED_OVERRIDE,
                'reviewed_by' => $status === ContentModerationResult::STATUS_APPROVED_OVERRIDE ? $user->id : null,
                'reviewed_at' => $status === ContentModerationResult::STATUS_APPROVED_OVERRIDE ? now()->subHours(rand(1, 48)) : null,
                'model_version' => 'claude-sonnet-4-20250514',
                'processing_time_ms' => rand(800, 3000),
                'token_count' => rand(500, 2000),
                'created_at' => now()->subHours(rand(1, 168)),
            ]);

            // Update the moderatable if it exists
            if ($moderatable) {
                $moderatable->update([
                    'moderation_status' => $status,
                    'latest_moderation_id' => $result->id,
                ]);
            }

            // Create queue item for items that need review (flagged or pending status)
            if (in_array($status, [ContentModerationResult::STATUS_FLAGGED, ContentModerationResult::STATUS_PENDING])) {
                $priorities = [
                    ModerationQueueItem::PRIORITY_LOW,
                    ModerationQueueItem::PRIORITY_NORMAL,
                    ModerationQueueItem::PRIORITY_NORMAL,
                    ModerationQueueItem::PRIORITY_HIGH,
                    ModerationQueueItem::PRIORITY_URGENT,
                ];

                ModerationQueueItem::create([
                    'org_id' => $orgId,
                    'moderation_result_id' => $result->id,
                    'status' => ModerationQueueItem::STATUS_PENDING,
                    'assigned_to' => $user->id,
                    'assigned_by' => $user->id,
                    'assigned_at' => now(),
                    'due_at' => now()->addHours(rand(4, 72)),
                    'priority' => $priorities[array_rand($priorities)],
                ]);
            }

            $created++;
        }

        $this->info("Created {$created} demo moderation results.");

        // Show summary
        $this->table(
            ['Status', 'Count'],
            ContentModerationResult::where('org_id', $orgId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->map(fn ($r) => [$r->status, $r->count])
                ->toArray()
        );

        return Command::SUCCESS;
    }

    protected function generateScoresForStatus(string $status): array
    {
        switch ($status) {
            case ContentModerationResult::STATUS_PASSED:
                return [
                    'overall' => rand(85, 98) / 100,
                    'age' => rand(80, 100) / 100,
                    'clinical' => rand(85, 100) / 100,
                    'cultural' => rand(80, 100) / 100,
                    'accuracy' => rand(85, 100) / 100,
                ];
            case ContentModerationResult::STATUS_FLAGGED:
                return [
                    'overall' => rand(70, 84) / 100,
                    'age' => rand(60, 90) / 100,
                    'clinical' => rand(65, 95) / 100,
                    'cultural' => rand(55, 85) / 100,
                    'accuracy' => rand(70, 90) / 100,
                ];
            case ContentModerationResult::STATUS_REJECTED:
                return [
                    'overall' => rand(25, 45) / 100,
                    'age' => rand(20, 60) / 100,
                    'clinical' => rand(15, 50) / 100,
                    'cultural' => rand(30, 55) / 100,
                    'accuracy' => rand(25, 50) / 100,
                ];
            case ContentModerationResult::STATUS_APPROVED_OVERRIDE:
                return [
                    'overall' => rand(65, 80) / 100,
                    'age' => rand(60, 85) / 100,
                    'clinical' => rand(70, 90) / 100,
                    'cultural' => rand(55, 80) / 100,
                    'accuracy' => rand(65, 85) / 100,
                ];
            default:
                return [
                    'overall' => rand(50, 100) / 100,
                    'age' => rand(50, 100) / 100,
                    'clinical' => rand(50, 100) / 100,
                    'cultural' => rand(50, 100) / 100,
                    'accuracy' => rand(50, 100) / 100,
                ];
        }
    }
}
