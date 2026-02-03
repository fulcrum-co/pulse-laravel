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
            'learner' => self::getLearnerConversations(),
            'teacher' => self::getTeacherConversations(),
            'counselor' => self::getCounselorConversations(),
            'parent' => self::getParentConversations(),
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
            'conv_teacher_1' => self::getTeacherTherapistMessages(),
            'conv_teacher_2' => self::getTeacherTutorMessages(),
            'conv_learner_1' => self::getLearnerCounselorMessages(),
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
        $providers = $this->demoDataProvider->getProviders();
        $learners = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_staff_1',
                'provider' => $providers[0], // Dr. Sarah Chen
                'learner' => $learners[0], // About Emma
                'last_message' => "I'd recommend we schedule a follow-up session next week to check on Emma's progress.",
                'last_message_at' => Carbon::now()->subMinutes(5),
                'unread_count' => 2,
                'stream_channel_id' => 'provider_1_user_1',
            ],
            [
                'id' => 'conv_staff_2',
                'provider' => $providers[1], // James Miller
                'learner' => $learners[1], // About Liam
                'last_message' => 'Liam has been making great progress with algebra. His test scores are improving.',
                'last_message_at' => Carbon::now()->subHours(2),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_user_1',
            ],
        ];
    }

    /**
     * Teacher conversations.
     */
    protected function getTeacherConversations(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $learners = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_teacher_1',
                'provider' => $providers[0], // Dr. Sarah Chen (therapist)
                'learner' => $learners[0], // About Emma
                'last_message' => "Thank you for the update on Emma. I'll adjust my classroom approach accordingly.",
                'last_message_at' => Carbon::now()->subMinutes(15),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_1_teacher_1',
            ],
            [
                'id' => 'conv_teacher_2',
                'provider' => $providers[1], // James Miller (tutor)
                'learner' => $learners[1], // About Liam
                'last_message' => 'Absolutely, I can coordinate on his homework assignments. When would you like to have a call?',
                'last_message_at' => Carbon::now()->subHours(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_teacher_1',
            ],
            [
                'id' => 'conv_teacher_3',
                'provider' => $providers[2], // Dr. Emily Rodriguez
                'learner' => null,
                'last_message' => "I'd love to discuss strategies for supporting learners with learning differences.",
                'last_message_at' => Carbon::now()->subDays(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_3_teacher_1',
            ],
        ];
    }

    /**
     * Learner conversations.
     */
    protected function getLearnerConversations(): array
    {
        $providers = $this->demoDataProvider->getProviders();

        return [
            [
                'id' => 'conv_learner_1',
                'provider' => $providers[3], // Marcus Thompson (college advisor)
                'learner' => null,
                'last_message' => 'Your college essay draft looks great! I have a few suggestions for the introduction.',
                'last_message_at' => Carbon::now()->subMinutes(30),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_4_learner_1',
            ],
            [
                'id' => 'conv_learner_2',
                'provider' => $providers[1], // James Miller (tutor)
                'learner' => null,
                'last_message' => "See you tomorrow at 4pm for our calculus session! Don't forget to review chapter 5.",
                'last_message_at' => Carbon::now()->subHours(3),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_learner_1',
            ],
        ];
    }

    /**
     * Counselor conversations.
     */
    protected function getCounselorConversations(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $learners = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_counselor_1',
                'provider' => $providers[0], // Dr. Sarah Chen
                'learner' => $learners[2], // About Sophia
                'last_message' => "I've completed the initial assessment. Sophia would benefit from anxiety coping strategies.",
                'last_message_at' => Carbon::now()->subMinutes(45),
                'unread_count' => 3,
                'stream_channel_id' => 'provider_1_counselor_1',
            ],
            [
                'id' => 'conv_counselor_2',
                'provider' => $providers[2], // Dr. Emily Rodriguez
                'learner' => $learners[0], // About Emma
                'last_message' => 'The learning assessment results are in. Can we schedule a video call to discuss?',
                'last_message_at' => Carbon::now()->subHours(4),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_3_counselor_1',
            ],
        ];
    }

    /**
     * Parent conversations.
     */
    protected function getParentConversations(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $learners = $this->demoDataProvider->getLearners();

        return [
            [
                'id' => 'conv_parent_1',
                'provider' => $providers[1], // James Miller (tutor)
                'learner' => $learners[0], // About their child
                'last_message' => "Emma did fantastic today! We covered quadratic equations and she's really getting it.",
                'last_message_at' => Carbon::now()->subMinutes(20),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_2_parent_1',
            ],
        ];
    }

    /**
     * Teacher-Therapist conversation messages.
     */
    protected function getTeacherTherapistMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[0];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hi Dr. Chen, I'm reaching out about Emma Johnson. I've noticed some changes in her classroom behavior lately - she seems more withdrawn and anxious during group activities.",
                'user' => ['id' => 'user_current', 'name' => 'Mrs. Thompson'],
                'created_at' => $now->copy()->subHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => "Thank you for reaching out, Mrs. Thompson. Your observations are really helpful. I've been working with Emma on anxiety management, and it's valuable to know how it's manifesting in the classroom.",
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->subMinutes(45)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => "With Emma's permission, I can share some strategies that might help in the classroom. Would that be useful?",
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->subMinutes(40)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => "That would be incredibly helpful! I want to make sure I'm supporting her in the best way possible. Should we schedule a video call to discuss in more detail?",
                'user' => ['id' => 'user_current', 'name' => 'Mrs. Thompson'],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_5',
                'text' => "Absolutely! I'm available tomorrow at 3:30 PM or Friday at 2:00 PM. You can click the video call button above to start a session whenever we're both ready. 📹",
                'user' => ['id' => 'provider_demo_1', 'name' => $provider['name']],
                'created_at' => $now->copy()->subMinutes(15)->toIso8601String(),
            ],
        ];
    }

    /**
     * Teacher-Tutor conversation messages.
     */
    protected function getTeacherTutorMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[1];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hi James! I'm Liam's math teacher. I wanted to coordinate with you on his tutoring progress. He mentioned you've been working on algebra with him.",
                'user' => ['id' => 'user_current', 'name' => 'Mr. Rodriguez'],
                'created_at' => $now->copy()->subHours(4)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => "Hi Mr. Rodriguez! Great to connect. Yes, Liam and I have been meeting twice a week. He's made significant progress - especially with linear equations.",
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(3)->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => "That's wonderful to hear! We're about to start the quadratics unit in class. Would it be possible to align our teaching approaches?",
                'user' => ['id' => 'user_current', 'name' => 'Mr. Rodriguez'],
                'created_at' => $now->copy()->subHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => 'Absolutely, I can coordinate on his homework assignments. When would you like to have a call?',
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->toIso8601String(),
            ],
        ];
    }

    /**
     * Learner-Counselor conversation messages.
     */
    protected function getLearnerCounselorMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[3];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hi Marcus! My counselor suggested I reach out about college applications. I'm feeling pretty overwhelmed with the process.",
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subDays(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => "Hey there! I totally understand - college apps can feel like a lot. Let's break it down together. First, have you thought about what kind of organizations you're interested in?",
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subDays(2)->addHours(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => "I've been looking at some UC organizations and a few private colleges. I'm interested in computer science or engineering.",
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subDays(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => "Great choices! For CS/Engineering, those are excellent options. Let's start working on your personal statement. Can you share your draft with me? I'll give you detailed feedback.",
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subDays(1)->addHours(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_5',
                'text' => "I just uploaded my draft. It's about my robotics club experience and how it sparked my interest in AI.",
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(3)->toIso8601String(),
            ],
            [
                'id' => 'msg_6',
                'text' => 'Your college essay draft looks great! I have a few suggestions for the introduction. Want to hop on a quick video call to go over them together?',
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
        ];
    }

    /**
     * Learner-Tutor conversation messages.
     */
    protected function getLearnerTutorMessages(): array
    {
        $providers = $this->demoDataProvider->getProviders();
        $provider = $providers[1];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hey James, I'm stuck on problem 5 in chapter 5. The limit problems are confusing me 😅",
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(5)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => 'No worries, limits can be tricky! Let me explain - for problem 5, you want to factor the numerator first. Try factoring x² - 4 as (x+2)(x-2).',
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(4)->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => 'Oh! That cancels out the (x-2) in the denominator! I got it now. Thanks!',
                'user' => ['id' => 'learner_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(4)->toIso8601String(),
            ],
            [
                'id' => 'msg_4',
                'text' => "Exactly! You've got it. 🎉 Keep practicing those factoring techniques. See you tomorrow at 4pm for our calculus session! Don't forget to review chapter 5.",
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

        return [
            [
                'id' => 'msg_default_1',
                'text' => 'Welcome! How can I help you today?',
                'user' => ['id' => 'provider_demo', 'name' => 'Provider'],
                'created_at' => $now->copy()->subHours(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_default_2',
                'text' => "Hi! I'd like to discuss scheduling a session.",
                'user' => ['id' => 'user_current', 'name' => 'You'],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_default_3',
                'text' => "Of course! I have availability this week. You can book directly using the 'Book Session' button above, or we could start with a quick video call to discuss your needs.",
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
     * Create a demo learner object (using stdClass for Livewire serialization).
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
