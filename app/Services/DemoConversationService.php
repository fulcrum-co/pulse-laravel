<?php

namespace App\Services;

use Carbon\Carbon;
use stdClass;

class DemoConversationService
{
    /**
     * Demo providers for conversations.
     */
    protected static array $demoProviders = [
        [
            'id' => 'demo_1',
            'name' => 'Dr. Sarah Chen',
            'display_name' => 'Dr. Sarah Chen, LCSW',
            'provider_type' => 'therapist',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => true,
        ],
        [
            'id' => 'demo_2',
            'name' => 'James Miller',
            'display_name' => 'James Miller - Math Tutor',
            'provider_type' => 'tutor',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => false,
        ],
        [
            'id' => 'demo_3',
            'name' => 'Dr. Emily Rodriguez',
            'display_name' => 'Dr. Emily Rodriguez, PhD',
            'provider_type' => 'psychologist',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => true,
        ],
        [
            'id' => 'demo_4',
            'name' => 'Marcus Thompson',
            'display_name' => 'Marcus Thompson - College Advisor',
            'provider_type' => 'counselor',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
            'verified' => false,
            'online' => true,
        ],
    ];

    /**
     * Demo students for conversations.
     */
    protected static array $demoStudents = [
        ['id' => 'student_101', 'name' => 'Emma Johnson', 'grade' => '10th Grade'],
        ['id' => 'student_102', 'name' => 'Liam Williams', 'grade' => '11th Grade'],
        ['id' => 'student_103', 'name' => 'Sophia Davis', 'grade' => '9th Grade'],
    ];

    /**
     * Get demo conversations for the current user/role.
     */
    public static function getConversations(?string $role = null): array
    {
        $role = $role ?? self::getEffectiveRole();

        return match ($role) {
            'student' => self::getStudentConversations(),
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
            'conv_student_1' => self::getStudentCounselorMessages(),
            'conv_student_2' => self::getStudentTutorMessages(),
            default => self::getDefaultMessages($conversationId),
        };
    }

    /**
     * Get available providers for new conversations.
     */
    public static function getAvailableProviders(?string $role = null): array
    {
        return array_slice(self::$demoProviders, 2); // Return last 2 as "available"
    }

    /**
     * Staff conversations (default).
     */
    protected static function getStaffConversations(): array
    {
        return [
            [
                'id' => 'conv_staff_1',
                'provider' => self::$demoProviders[0], // Dr. Sarah Chen
                'student' => self::$demoStudents[0], // About Emma
                'last_message' => "I'd recommend we schedule a follow-up session next week to check on Emma's progress.",
                'last_message_at' => Carbon::now()->subMinutes(5),
                'unread_count' => 2,
                'stream_channel_id' => 'provider_1_user_1',
            ],
            [
                'id' => 'conv_staff_2',
                'provider' => self::$demoProviders[1], // James Miller
                'student' => self::$demoStudents[1], // About Liam
                'last_message' => "Liam has been making great progress with algebra. His test scores are improving.",
                'last_message_at' => Carbon::now()->subHours(2),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_user_1',
            ],
        ];
    }

    /**
     * Teacher conversations.
     */
    protected static function getTeacherConversations(): array
    {
        return [
            [
                'id' => 'conv_teacher_1',
                'provider' => self::$demoProviders[0], // Dr. Sarah Chen (therapist)
                'student' => self::$demoStudents[0], // About Emma
                'last_message' => "Thank you for the update on Emma. I'll adjust my classroom approach accordingly.",
                'last_message_at' => Carbon::now()->subMinutes(15),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_1_teacher_1',
            ],
            [
                'id' => 'conv_teacher_2',
                'provider' => self::$demoProviders[1], // James Miller (tutor)
                'student' => self::$demoStudents[1], // About Liam
                'last_message' => "Absolutely, I can coordinate on his homework assignments. When would you like to have a call?",
                'last_message_at' => Carbon::now()->subHours(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_teacher_1',
            ],
            [
                'id' => 'conv_teacher_3',
                'provider' => self::$demoProviders[2], // Dr. Emily Rodriguez
                'student' => null,
                'last_message' => "I'd love to discuss strategies for supporting students with learning differences.",
                'last_message_at' => Carbon::now()->subDays(1),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_3_teacher_1',
            ],
        ];
    }

    /**
     * Student conversations.
     */
    protected static function getStudentConversations(): array
    {
        return [
            [
                'id' => 'conv_student_1',
                'provider' => self::$demoProviders[3], // Marcus Thompson (college advisor)
                'student' => null,
                'last_message' => "Your college essay draft looks great! I have a few suggestions for the introduction.",
                'last_message_at' => Carbon::now()->subMinutes(30),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_4_student_1',
            ],
            [
                'id' => 'conv_student_2',
                'provider' => self::$demoProviders[1], // James Miller (tutor)
                'student' => null,
                'last_message' => "See you tomorrow at 4pm for our calculus session! Don't forget to review chapter 5.",
                'last_message_at' => Carbon::now()->subHours(3),
                'unread_count' => 0,
                'stream_channel_id' => 'provider_2_student_1',
            ],
        ];
    }

    /**
     * Counselor conversations.
     */
    protected static function getCounselorConversations(): array
    {
        return [
            [
                'id' => 'conv_counselor_1',
                'provider' => self::$demoProviders[0], // Dr. Sarah Chen
                'student' => self::$demoStudents[2], // About Sophia
                'last_message' => "I've completed the initial assessment. Sophia would benefit from anxiety coping strategies.",
                'last_message_at' => Carbon::now()->subMinutes(45),
                'unread_count' => 3,
                'stream_channel_id' => 'provider_1_counselor_1',
            ],
            [
                'id' => 'conv_counselor_2',
                'provider' => self::$demoProviders[2], // Dr. Emily Rodriguez
                'student' => self::$demoStudents[0], // About Emma
                'last_message' => "The learning assessment results are in. Can we schedule a video call to discuss?",
                'last_message_at' => Carbon::now()->subHours(4),
                'unread_count' => 1,
                'stream_channel_id' => 'provider_3_counselor_1',
            ],
        ];
    }

    /**
     * Parent conversations.
     */
    protected static function getParentConversations(): array
    {
        return [
            [
                'id' => 'conv_parent_1',
                'provider' => self::$demoProviders[1], // James Miller (tutor)
                'student' => self::$demoStudents[0], // About their child
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
    protected static function getTeacherTherapistMessages(): array
    {
        $provider = self::$demoProviders[0];
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
    protected static function getTeacherTutorMessages(): array
    {
        $provider = self::$demoProviders[1];
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
                'text' => "Absolutely, I can coordinate on his homework assignments. When would you like to have a call?",
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(1)->toIso8601String(),
            ],
        ];
    }

    /**
     * Student-Counselor conversation messages.
     */
    protected static function getStudentCounselorMessages(): array
    {
        $provider = self::$demoProviders[3];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hi Marcus! My counselor suggested I reach out about college applications. I'm feeling pretty overwhelmed with the process.",
                'user' => ['id' => 'student_current', 'name' => 'You'],
                'created_at' => $now->copy()->subDays(2)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => "Hey there! I totally understand - college apps can feel like a lot. Let's break it down together. First, have you thought about what kind of schools you're interested in?",
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subDays(2)->addHours(1)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => "I've been looking at some UC schools and a few private colleges. I'm interested in computer science or engineering.",
                'user' => ['id' => 'student_current', 'name' => 'You'],
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
                'user' => ['id' => 'student_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(3)->toIso8601String(),
            ],
            [
                'id' => 'msg_6',
                'text' => "Your college essay draft looks great! I have a few suggestions for the introduction. Want to hop on a quick video call to go over them together?",
                'user' => ['id' => 'provider_demo_4', 'name' => $provider['name']],
                'created_at' => $now->copy()->subMinutes(30)->toIso8601String(),
            ],
        ];
    }

    /**
     * Student-Tutor conversation messages.
     */
    protected static function getStudentTutorMessages(): array
    {
        $provider = self::$demoProviders[1];
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_1',
                'text' => "Hey James, I'm stuck on problem 5 in chapter 5. The limit problems are confusing me 😅",
                'user' => ['id' => 'student_current', 'name' => 'You'],
                'created_at' => $now->copy()->subHours(5)->toIso8601String(),
            ],
            [
                'id' => 'msg_2',
                'text' => "No worries, limits can be tricky! Let me explain - for problem 5, you want to factor the numerator first. Try factoring x² - 4 as (x+2)(x-2).",
                'user' => ['id' => 'provider_demo_2', 'name' => $provider['name']],
                'created_at' => $now->copy()->subHours(4)->subMinutes(30)->toIso8601String(),
            ],
            [
                'id' => 'msg_3',
                'text' => "Oh! That cancels out the (x-2) in the denominator! I got it now. Thanks!",
                'user' => ['id' => 'student_current', 'name' => 'You'],
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
    protected static function getDefaultMessages(string $conversationId): array
    {
        $now = Carbon::now();

        return [
            [
                'id' => 'msg_default_1',
                'text' => "Welcome! How can I help you today?",
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
        if (!$user) {
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
    public static function createDemoProvider(array $data): stdClass
    {
        $obj = new stdClass();
        $obj->id = $data['id'] ?? '';
        $obj->name = $data['name'] ?? '';
        $obj->display_name = $data['display_name'] ?? $data['name'] ?? '';
        $obj->provider_type = $data['provider_type'] ?? '';
        $obj->thumbnail_url = $data['thumbnail_url'] ?? '';
        $obj->verified = $data['verified'] ?? false;
        $obj->online = $data['online'] ?? false;
        return $obj;
    }

    /**
     * Create a demo student object (using stdClass for Livewire serialization).
     */
    public static function createDemoStudent(array $data): stdClass
    {
        $obj = new stdClass();
        $obj->id = $data['id'] ?? '';
        $obj->name = $data['name'] ?? '';
        $obj->full_name = $data['name'] ?? '';
        $obj->grade = $data['grade'] ?? '';
        return $obj;
    }

    /**
     * Create a demo conversation object (using stdClass for Livewire serialization).
     */
    public static function createDemoConversation(array $data): stdClass
    {
        $obj = new stdClass();
        $obj->id = $data['id'] ?? '';
        $obj->provider = self::createDemoProvider($data['provider'] ?? []);
        $obj->student = isset($data['student']) && $data['student'] ? self::createDemoStudent($data['student']) : null;
        $obj->last_message_preview = $data['last_message'] ?? '';
        $obj->last_message_at = $data['last_message_at'] ?? now();
        $obj->unread_count_initiator = $data['unread_count'] ?? 0;
        $obj->stream_channel_id = $data['stream_channel_id'] ?? '';
        $obj->stream_channel_type = 'messaging';
        $obj->provider_id = $data['provider']['id'] ?? '';
        return $obj;
    }

    /**
     * Helper to check if provider is verified (for use in templates).
     */
    public static function isVerified(stdClass $provider): bool
    {
        return $provider->verified ?? false;
    }

    /**
     * Helper to check if provider is online (for use in templates).
     */
    public static function isOnline(stdClass $provider): bool
    {
        return $provider->online ?? false;
    }
}
