<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Domain\DemoDataProviderService;
use Carbon\Carbon;

class DemoConversationService
{
    public function __construct(
        protected DemoDataProviderService $demoDataProvider
    ) {}

    /**
     * Get demo conversations for the current user/role.
     */
    public static function getConversations(?string $role = null): array
    {
        $role = $role ?? self::getEffectiveRole();

        return match ($role) {
            'participant' => self::getLearnerConversations(),
            'instructor' => self::getInstructorConversations(),
            'support_person' => self::getSupportPersonConversations(),
            'direct_supervisor' => self::getSupervisorConversations(),
            default => self::getStaffConversations(),
        };
    }

    /**
     * Get demo messages for a conversation.
     */
    public static function getMessages(string $conversationId, ?string $role = null): array
    {
        $role = $role ?? self::getEffectiveRole();

        return match ($conversationId) {
            'conv_instructor_1' => self::getInstructorTherapistMessages(),
            'conv_instructor_2' => self::getInstructorTutorMessages(),
            'conv_learner_1' => self::getLearnerSupportMessages(),
            'conv_learner_2' => self::getLearnerTutorMessages(),
            default => self::getDefaultMessages($conversationId),
        };
    }

    /**
     * Get available providers for new conversations.
     */
    public static function getAvailableProviders(?string $role = null): array
    {
        $providers = app(DemoDataProviderService::class)->getProviders();
        return array_slice($providers, 2); // Return last 2 as "available"
    }

    /**
     * Staff conversations (default).
     */
    protected function getStaffConversations(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $providers = $this->demoDataProvider->getProviders();
        $participants = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_staff_1',
                'provider' => $providers[0], // Dr. Sarah Chen
                'participant' => $participants[0], // About Emma
                'last_message' => $terminology->get('demo_message_follow_up'),
                'last_message_at' => Carbon::now()->subMinutes(5),
                'unread_count' => 2,
                'stream_channel_id' => 'provider_1_user_1',
            ],
            [
                'id' => 'conv_staff_2',
                'provider' => $providers[1], // James Miller
                'participant' => $participants[1], // About Liam
                'last_message' => $terminology->get('demo_message_progress_update'),
                'last_message_at' => Carbon::now()->subHours(2),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_user_1',
            ],
        ];
    }

    /**
     * Instructor conversations.
     */
    protected function getInstructorConversations(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $providers = $this->demoDataProvider->getProviders();
        $participants = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_instructor_1',
                'provider' => $providers[0], // Dr. Sarah Chen (therapist)
                'participant' => $participants[0], // About Emma
                'last_message' => $terminology->get('demo_message_update_adjust_approach'),
                'last_message_at' => Carbon::now()->subMinutes(15),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_1_instructor_1',
            ],
            [
                'id' => 'conv_instructor_2',
                'provider' => $providers[1], // James Miller (tutor)
                'participant' => $participants[1], // About Liam
                'last_message' => $terminology->get('demo_message_coordinate_tasks'),
                'last_message_at' => Carbon::now()->subHours(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_instructor_1',
            ],
            [
                'id' => 'conv_instructor_3',
                'provider' => $providers[2], // Dr. Emily Rodriguez
                'participant' => null,
                'last_message' => $terminology->get('demo_message_supporting_needs'),
                'last_message_at' => Carbon::now()->subDays(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_3_instructor_1',
            ],
        ];
    }

    /**
     * Participant conversations.
     */
    protected function getLearnerConversations(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $providers = $this->demoDataProvider->getProviders();

        return [
            [
                'id' => 'conv_learner_1',
                'provider' => $providers[3], // Marcus Thompson (college advisor)
                'participant' => null,
                'last_message' => $terminology->get('demo_message_draft_feedback'),
                'last_message_at' => Carbon::now()->subMinutes(30),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_4_learner_1',
            ],
            [
                'id' => 'conv_learner_2',
                'provider' => $providers[1], // James Miller (tutor)
                'participant' => null,
                'last_message' => $terminology->get('demo_message_session_reminder'),
                'last_message_at' => Carbon::now()->subHours(3),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_learner_1',
            ],
        ];
    }

    /**
     * Support Person conversations.
     */
    protected function getSupportPersonConversations(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $providers = $this->demoDataProvider->getProviders();
        $participants = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_support_1',
                'provider' => $providers[0], // Dr. Sarah Chen
                'participant' => $participants[2], // About Sophia
                'last_message' => $terminology->get('demo_message_assessment_complete'),
                'last_message_at' => Carbon::now()->subMinutes(45),
                'unread_count' => 3,
                'stream_channel_id' => 'provider_1_support_1',
            ],
            [
                'id' => 'conv_support_2',
                'provider' => $providers[2], // Dr. Emily Rodriguez
                'participant' => $participants[0], // About Emma
                'last_message' => $terminology->get('demo_message_results_ready'),
                'last_message_at' => Carbon::now()->subHours(4),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_3_support_1',
            ],
        ];
    }

    /**
     * Direct Supervisor conversations.
     */
    protected function getSupervisorConversations(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $providers = $this->demoDataProvider->getProviders();
        $participants = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_supervisor_1',
                'provider' => $providers[1], // James Miller (tutor)
                'participant' => $participants[0], // About their child
                'last_message' => $terminology->get('demo_message_great_progress'),
                'last_message_at' => Carbon::now()->subMinutes(20),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_2_supervisor_1',
            ],
        ];
    }

    /**
     * Instructor-Therapist conversation messages.
     */
    protected function getInstructorTherapistMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[0];
        $now = Carbon::now();
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => 'msg_1',
                'text' => $terminology->get('demo_message_outreach_observations'),
                'user' => ['id' => 'user_current', 'name' => 'Mrs. Thompson'],
                'created_at' => $now->copy()->subHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => $terminology->get('demo_message_response_observations'),
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->subMinutes(45)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => $terminology->get('demo_message_share_strategies'),
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->subMinutes(40)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => $terminology->get('demo_message_schedule_call'),
                'user' => ['id' => 'user_current', 'name' => 'Mrs. Thompson'],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_5',
                'text' => $terminology->get('demo_message_availability_call'),
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subMinutes(15)->toIso8601String(),
            ],
        ];
    }

    /**
     * Instructor-Tutor conversation messages.
     */
    protected function getInstructorTutorMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[1];
        $now = Carbon::now();
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => 'msg_1',
                'text' => $terminology->get('demo_message_instructor_intro'),
                'user' => ['id' => 'user_current', 'name' => 'Mr. Rodriguez'],
                'created_at' => $now->copy()->subHours(4)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => $terminology->get('demo_message_provider_intro_progress'),
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(3)->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => $terminology->get('demo_message_align_approach'),
                'user' => ['id' => 'user_current', 'name' => 'Mr. Rodriguez'],
                'created_at' => $now->copy()->subHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => $terminology->get('demo_message_coordinate_call'),
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->toIso8601String(),
            ],
        ];
    }

    /**
     * Participant-Support Person conversation messages.
     */
    protected function getLearnerSupportMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[3];
        $now = Carbon::now();
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => 'msg_1',
                'text' => $terminology->get('demo_message_support_person_outreach'),
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subDays(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => $terminology->get('demo_message_support_person_response'),
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subDays(2)->addHours(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => $terminology->get('demo_message_interest_areas'),
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subDays(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => $terminology->get('demo_message_request_draft'),
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subDays(1)->addHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_5',
                'text' => $terminology->get('demo_message_uploaded_draft'),
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(3)->toIso8601String(),
            ],
            [
                'id' => 'msg_6',
                'text' => $terminology->get('demo_message_feedback_call'),
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
        ];
    }

    /**
     * Participant-Tutor conversation messages.
     */
    protected function getLearnerTutorMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[1];
        $now = Carbon::now();
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => 'msg_1',
                'text' => $terminology->get('demo_message_stuck_problem'),
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(5)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => $terminology->get('demo_message_explain_solution'),
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(4)->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => $terminology->get('demo_message_acknowledge_solution'),
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(4)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => $terminology->get('demo_message_practice_reminder'),
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(3)->toIso8601String(),
            ],
        ];
    }

    /**
     * Default messages for any conversation.
     */
    protected function getDefaultMessages(string $conversationId): array
    {
        $now = Carbon::now();
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            [
                'id' => 'msg_default_1',
                'text' => $terminology->get('demo_message_default_welcome'),
                'user' => ['id' => 'provider_demo', 'name' => 'Provider'],
                'created_at' => $now->copy()->subHours(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_default_2',
                'text' => $terminology->get('demo_message_default_schedule'),
                'user' => ['id' => 'user_current', 'name' => 'You'],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_default_3',
                'text' => $terminology->get('demo_message_default_availability'),
                'user' => ['id' => 'provider_demo', 'name' => 'Provider'],
                'created_at' => $now->copy()->subMinutes(15)->toIso8601String(),
            ],
        ];
    }

    /**
     * Get effective role from session or user.
     */
    protected static function getEffectiveRole(): string
    {
        $user = auth()->user();
        if (! $user) {
            return 'guest';
        }

        // Check for demo role override
        $demoRole = session('demo_role_override');
        if ($demoRole && $demoRole !== 'actual') {
            return $demoRole;
        }

        return $user->primary_role ?? 'staff';
    }

    /**
     * Create a demo provider object (using stdClass for Livewire serialization).
     */
    public function createDemoProvider(array $data)
    {
        return $this->demoDataProvider->createProvider($data);
    }

    /**
     * Create a demo participant object (using stdClass for Livewire serialization).
     */
    public function createDemoLearner(array $data)
    {
        return $this->demoDataProvider->createLearner($data);
    }

    /**
     * Create a demo conversation object (using stdClass for Livewire serialization).
     */
    public function createDemoConversation(array $data)
    {
        return $this->demoDataProvider->createConversation($data);
    }

    /**
     * Helper to check if provider is verified (for use in templates).
     */
    public function isVerified($provider): bool
    {
        return $this->demoDataProvider->isProviderVerified($provider);
    }

    /**
     * Helper to check if provider is online (for use in templates).
     */
    public function isOnline($provider): bool
    {
        return $this->demoDataProvider->isProviderOnline($provider);
    }
}
