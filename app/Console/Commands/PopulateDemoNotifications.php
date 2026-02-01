<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\CustomReport;
use App\Models\MiniCourse;
use App\Models\StrategicPlan;
use App\Models\Student;
use App\Models\Survey;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Workflow;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class PopulateDemoNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:demo {--user= : User ID to create notifications for} {--email= : User email to create notifications for} {--count=20 : Number of notifications to create} {--fresh : Clear existing notifications first}';

    /**
     * The console command description.
     */
    protected $description = 'Populate demo notification data for testing';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');
        $email = $this->option('email');
        $count = (int) $this->option('count');

        if ($email) {
            // Find by email
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("User with email '{$email}' not found.");
                return Command::FAILURE;
            }
            $userId = $user->id;
        } elseif (!$userId) {
            // Get first admin user or first user
            $user = User::where('primary_role', 'admin')->first() ?? User::first();
            if (!$user) {
                $this->error('No users found in the database.');
                return Command::FAILURE;
            }
            $userId = $user->id;
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return Command::FAILURE;
            }
        }

        $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $this->info("Creating {$count} demo notifications for user: {$userName} (ID: {$userId})");

        // Clear existing notifications if --fresh flag is set
        if ($this->option('fresh')) {
            $deleted = UserNotification::where('user_id', $userId)->delete();
            $this->info("Cleared {$deleted} existing notifications.");
        }

        $created = 0;
        $demoNotifications = $this->getDemoNotifications();

        for ($i = 0; $i < $count; $i++) {
            $demo = $demoNotifications[$i % count($demoNotifications)];

            $notification = $this->notificationService->notify(
                $userId,
                $demo['category'],
                $demo['type'],
                array_merge($demo['data'], [
                    'created_at' => now()->subMinutes(rand(5, 60 * 24 * 7)), // Random time in last week
                ])
            );

            if ($notification) {
                // Randomly set some statuses
                $statusRoll = rand(1, 10);
                if ($statusRoll <= 3) {
                    // 30% read
                    $notification->update([
                        'status' => UserNotification::STATUS_READ,
                        'read_at' => now()->subMinutes(rand(1, 60)),
                    ]);
                } elseif ($statusRoll == 4) {
                    // 10% snoozed
                    $notification->update([
                        'status' => UserNotification::STATUS_SNOOZED,
                        'snoozed_until' => now()->addHours(rand(1, 24)),
                    ]);
                }
                // 60% remain unread

                $created++;
            }
        }

        $this->info("Successfully created {$created} demo notifications.");

        // Show summary by category
        $this->newLine();
        $this->info('Summary by category:');
        $categories = UserNotification::where('user_id', $userId)
            ->selectRaw('category, COUNT(*) as count, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as unread', [UserNotification::STATUS_UNREAD])
            ->groupBy('category')
            ->get();

        $this->table(
            ['Category', 'Total', 'Unread'],
            $categories->map(fn($c) => [$c->category, $c->count, $c->unread])
        );

        return Command::SUCCESS;
    }

    /**
     * Get demo notification definitions with real database IDs.
     */
    protected function getDemoNotifications(): array
    {
        // Get real data from database for specific URLs
        $surveys = Survey::limit(3)->pluck('title', 'id')->toArray();
        $collections = Collection::limit(3)->pluck('title', 'id')->toArray();
        $courses = MiniCourse::limit(3)->pluck('title', 'id')->toArray();
        $reports = CustomReport::limit(3)->pluck('report_name', 'id')->toArray();
        $workflows = Workflow::limit(3)->pluck('name', 'id')->toArray();
        // Students may be linked to users table via user_id - get student IDs and try to get names from user relationship
        $students = Student::with('user')->limit(3)->get(['id', 'user_id', 'student_number'])->toArray();
        $strategies = StrategicPlan::limit(3)->pluck('title', 'id')->toArray();

        $notifications = [];

        // Survey notifications - use actual survey IDs
        foreach ($surveys as $id => $title) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_SURVEY,
                'type' => 'survey_assigned',
                'data' => [
                    'title' => "Complete survey: {$title}",
                    'body' => 'You have been assigned this survey. Please complete it before the deadline.',
                    'icon' => 'clipboard-document-list',
                    'priority' => UserNotification::PRIORITY_HIGH,
                    'action_url' => "/surveys/{$id}",
                    'action_label' => 'Take Survey',
                    'expires_at' => now()->addDays(7),
                ],
            ];
        }

        // Collection notifications - use actual collection IDs
        foreach ($collections as $id => $name) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_COLLECTION,
                'type' => 'collection_reminder',
                'data' => [
                    'title' => "Data collection due: {$name}",
                    'body' => 'Reminder to complete your data collection entry.',
                    'icon' => 'circle-stack',
                    'priority' => UserNotification::PRIORITY_HIGH,
                    'action_url' => "/collect/{$id}",
                    'action_label' => 'Enter Data',
                ],
            ];
        }

        // Course notifications - use actual course IDs
        foreach ($courses as $id => $title) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_COURSE,
                'type' => 'course_enrolled',
                'data' => [
                    'title' => "Continue course: {$title}",
                    'body' => 'You have progress to make on this course. Continue learning!',
                    'icon' => 'academic-cap',
                    'priority' => UserNotification::PRIORITY_NORMAL,
                    'action_url' => "/resources/courses/{$id}",
                    'action_label' => 'Continue Course',
                ],
            ];
        }

        // Report notifications - use actual report IDs
        foreach ($reports as $id => $name) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_REPORT,
                'type' => 'report_published',
                'data' => [
                    'title' => "Review report: {$name}",
                    'body' => 'This report is ready for your review.',
                    'icon' => 'document-chart-bar',
                    'priority' => UserNotification::PRIORITY_NORMAL,
                    'action_url' => "/reports/{$id}/preview",
                    'action_label' => 'View Report',
                ],
            ];
        }

        // Workflow/Alert notifications - use actual workflow IDs
        foreach ($workflows as $id => $name) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'workflow_triggered',
                'data' => [
                    'title' => "Alert triggered: {$name}",
                    'body' => 'This alert workflow was triggered and requires your attention.',
                    'icon' => 'bolt',
                    'priority' => UserNotification::PRIORITY_URGENT,
                    'action_url' => "/alerts/{$id}/canvas",
                    'action_label' => 'View Alert',
                ],
            ];
        }

        // Student contact notifications - use actual student IDs
        foreach ($students as $student) {
            // Try to get name from user relationship (first_name + last_name), fall back to student_number or ID
            $studentUser = $student['user'] ?? null;
            $name = $studentUser
                ? trim(($studentUser['first_name'] ?? '') . ' ' . ($studentUser['last_name'] ?? ''))
                : null;
            $name = $name ?: ($student['student_number'] ? "#{$student['student_number']}" : "#{$student['id']}");
            $notifications[] = [
                'category' => UserNotification::CATEGORY_WORKFLOW_ALERT,
                'type' => 'student_flagged',
                'data' => [
                    'title' => "Review student: {$name}",
                    'body' => 'This student has been flagged for review by an alert workflow.',
                    'icon' => 'user',
                    'priority' => UserNotification::PRIORITY_HIGH,
                    'action_url' => "/contacts/students/{$student['id']}",
                    'action_label' => 'View Student',
                ],
            ];
        }

        // Strategy/Plan notifications - use actual plan IDs
        foreach ($strategies as $id => $name) {
            $notifications[] = [
                'category' => UserNotification::CATEGORY_STRATEGY,
                'type' => 'activity_due_soon',
                'data' => [
                    'title' => "Plan update needed: {$name}",
                    'body' => 'This strategic plan has activities that need your attention.',
                    'icon' => 'clipboard-document-list',
                    'priority' => UserNotification::PRIORITY_HIGH,
                    'action_url' => "/strategies/{$id}",
                    'action_label' => 'View Plan',
                ],
            ];
        }

        // Add some generic system notifications (these don't need specific IDs)
        $notifications[] = [
            'category' => UserNotification::CATEGORY_SYSTEM,
            'type' => 'welcome',
            'data' => [
                'title' => 'Welcome to Pulse!',
                'body' => 'Get started by exploring your dashboard.',
                'icon' => 'hand-raised',
                'priority' => UserNotification::PRIORITY_LOW,
                'action_url' => '/dashboard',
                'action_label' => 'Go to Dashboard',
            ],
        ];

        // If we don't have enough real data, add fallback notifications to list pages
        if (count($notifications) < 5) {
            $fallbacks = [
                [
                    'category' => UserNotification::CATEGORY_SURVEY,
                    'type' => 'survey_reminder',
                    'data' => [
                        'title' => 'Surveys need attention',
                        'body' => 'You have surveys waiting for your response.',
                        'icon' => 'clipboard-document-list',
                        'priority' => UserNotification::PRIORITY_NORMAL,
                        'action_url' => '/surveys',
                        'action_label' => 'View Surveys',
                    ],
                ],
                [
                    'category' => UserNotification::CATEGORY_COLLECTION,
                    'type' => 'collection_reminder',
                    'data' => [
                        'title' => 'Data collections pending',
                        'body' => 'You have data collections waiting for entries.',
                        'icon' => 'circle-stack',
                        'priority' => UserNotification::PRIORITY_NORMAL,
                        'action_url' => '/collect',
                        'action_label' => 'View Collections',
                    ],
                ],
                [
                    'category' => UserNotification::CATEGORY_REPORT,
                    'type' => 'reports_available',
                    'data' => [
                        'title' => 'New reports available',
                        'body' => 'Check out the latest reports in your dashboard.',
                        'icon' => 'document-chart-bar',
                        'priority' => UserNotification::PRIORITY_LOW,
                        'action_url' => '/reports',
                        'action_label' => 'View Reports',
                    ],
                ],
                [
                    'category' => UserNotification::CATEGORY_STRATEGY,
                    'type' => 'strategy_update',
                    'data' => [
                        'title' => 'Strategy plan updates',
                        'body' => 'Review the latest updates to your strategic plans.',
                        'icon' => 'clipboard-document-list',
                        'priority' => UserNotification::PRIORITY_NORMAL,
                        'action_url' => '/strategies',
                        'action_label' => 'View Plans',
                    ],
                ],
            ];
            $notifications = array_merge($notifications, $fallbacks);
        }

        return $notifications;
    }
}
