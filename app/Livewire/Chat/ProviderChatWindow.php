<?php

namespace App\Livewire\Chat;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\DemoConversationService;
use App\Services\StreamChatService;
use Livewire\Component;
use Livewire\Attributes\On;

class ProviderChatWindow extends Component
{
    public ?object $conversation = null;
    public string $messageText = '';
    public array $messages = [];
    public bool $isLoading = false;
    public bool $showBookingModal = false;
    public bool $showVideoModal = false;
    public bool $isDemo = false;
    public string $videoCallState = 'idle'; // idle, connecting, connected, ended

    protected StreamChatService $streamService;

    public function boot(StreamChatService $streamService): void
    {
        $this->streamService = $streamService;
    }

    public function mount(?string $conversationId = null, bool $isDemo = true): void
    {
        $this->isDemo = $isDemo;
        if ($conversationId) {
            $this->loadConversation($conversationId, $isDemo);
        }
    }

    #[On('conversation-selected')]
    public function onConversationSelected(string $conversationId, bool $isDemo = true): void
    {
        $this->isDemo = $isDemo;
        $this->loadConversation($conversationId, $isDemo);
    }

    /**
     * Load a conversation and its messages.
     */
    public function loadConversation(string $conversationId, bool $isDemo = true): void
    {
        $this->isDemo = $isDemo;
        $this->showVideoModal = false;
        $this->videoCallState = 'idle';

        if ($isDemo || str_starts_with($conversationId, 'conv_')) {
            $this->loadDemoConversation($conversationId);
            return;
        }

        $this->conversation = ProviderConversation::with(['provider', 'student'])
            ->find($conversationId);

        if (!$this->conversation) {
            return;
        }

        // Mark as read
        $this->conversation->markReadByInitiator();

        // Load messages from GetStream
        $this->loadMessages();
    }

    /**
     * Load a demo conversation.
     */
    protected function loadDemoConversation(string $conversationId): void
    {
        $conversations = DemoConversationService::getConversations();

        foreach ($conversations as $conv) {
            if ($conv['id'] === $conversationId) {
                $this->conversation = DemoConversationService::createDemoConversation($conv);
                $this->messages = DemoConversationService::getMessages($conversationId);
                return;
            }
        }

        // If not found, use first conversation
        if (!empty($conversations)) {
            $conv = $conversations[0];
            $this->conversation = DemoConversationService::createDemoConversation($conv);
            $this->messages = DemoConversationService::getMessages($conv['id']);
        }
    }

    /**
     * Load messages from GetStream.
     */
    protected function loadMessages(): void
    {
        if (!$this->conversation || !$this->streamService->isConfigured()) {
            $this->messages = [];
            return;
        }

        try {
            $this->messages = $this->streamService->getChannelMessages(
                $this->conversation->stream_channel_type,
                $this->conversation->stream_channel_id,
                50
            );
        } catch (\Exception $e) {
            $this->messages = [];
        }
    }

    /**
     * Send a message.
     */
    public function sendMessage(): void
    {
        if (empty(trim($this->messageText))) {
            return;
        }

        if (!$this->conversation) {
            return;
        }

        $this->isLoading = true;
        $user = auth()->user();
        $messageContent = trim($this->messageText);

        // For demo mode, add message to local array
        if ($this->isDemo) {
            $this->messages[] = [
                'id' => 'msg_new_' . uniqid(),
                'text' => $messageContent,
                'user' => ['id' => 'user_current', 'name' => $user->full_name ?? 'You'],
                'created_at' => now()->toIso8601String(),
            ];
            $this->messageText = '';
            $this->isLoading = false;

            // Simulate provider response after 2 seconds
            $this->dispatch('demo-message-sent');
            return;
        }

        try {
            if ($this->streamService->isConfigured()) {
                // Send via GetStream
                $channel = $this->streamService->getClient()->Channel(
                    $this->conversation->stream_channel_type,
                    $this->conversation->stream_channel_id
                );

                $channel->sendMessage([
                    'text' => $messageContent,
                ], $this->streamService->getUserStreamId($user));
            }

            // Update local conversation
            $this->conversation->updateLastMessage(
                $messageContent,
                get_class($user),
                $user->id
            );

            // Increment provider's unread count
            $this->conversation->incrementProviderUnread();

            // Clear input and reload messages
            $this->messageText = '';
            $this->loadMessages();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        }

        $this->isLoading = false;
    }

    /**
     * Start a video call.
     */
    public function startVideoCall(): void
    {
        $this->showVideoModal = true;
        $this->videoCallState = 'connecting';

        // Simulate connecting
        $this->dispatch('video-call-started');
    }

    /**
     * Simulate connecting to call (for demo).
     */
    public function connectCall(): void
    {
        $this->videoCallState = 'connected';
    }

    /**
     * End the video call.
     */
    public function endVideoCall(): void
    {
        $this->videoCallState = 'ended';

        // Add system message about call
        if ($this->isDemo && $this->conversation) {
            $this->messages[] = [
                'id' => 'msg_call_' . uniqid(),
                'text' => '📹 Video call ended • Duration: 0:' . rand(30, 59),
                'user' => ['id' => 'system', 'name' => 'System'],
                'created_at' => now()->toIso8601String(),
                'is_system' => true,
            ];
        }
    }

    /**
     * Close video modal.
     */
    public function closeVideoModal(): void
    {
        $this->showVideoModal = false;
        $this->videoCallState = 'idle';
    }

    /**
     * Get the GetStream user token for the current user.
     */
    public function getStreamTokenProperty(): ?string
    {
        if (!$this->streamService->isConfigured()) {
            return null;
        }

        $user = auth()->user();
        $streamUserId = $this->streamService->getUserStreamId($user);

        try {
            // Ensure user exists in Stream
            $this->streamService->createOrUpdateUser($streamUserId, [
                'name' => $user->full_name,
                'image' => $user->avatar_url,
                'role' => $user->primary_role,
            ]);

            return $this->streamService->generateUserToken($streamUserId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Stream configuration for JavaScript client.
     */
    public function getStreamConfigProperty(): array
    {
        if ($this->isDemo || !$this->streamService->isConfigured()) {
            return ['configured' => false, 'isDemo' => true];
        }

        $user = auth()->user();

        return [
            'configured' => true,
            'isDemo' => false,
            'apiKey' => config('services.stream.api_key'),
            'userId' => $this->streamService->getUserStreamId($user),
            'userToken' => $this->streamToken,
            'channelType' => $this->conversation?->stream_channel_type ?? 'messaging',
            'channelId' => $this->conversation?->stream_channel_id,
        ];
    }

    /**
     * Open booking modal.
     */
    public function openBookingModal(): void
    {
        $this->showBookingModal = true;
        $this->dispatch('open-booking-modal', providerId: $this->conversation->provider_id ?? $this->conversation->provider->id);
    }

    /**
     * Close booking modal.
     */
    public function closeBookingModal(): void
    {
        $this->showBookingModal = false;
    }

    /**
     * Format timestamp for display.
     */
    public function formatMessageTime(string $timestamp): string
    {
        $date = \Carbon\Carbon::parse($timestamp);
        $now = now();

        if ($date->isToday()) {
            return $date->format('g:i A');
        } elseif ($date->isYesterday()) {
            return 'Yesterday ' . $date->format('g:i A');
        } elseif ($date->isCurrentWeek()) {
            return $date->format('l g:i A');
        } else {
            return $date->format('M j, g:i A');
        }
    }

    /**
     * Check if current user sent the message.
     */
    public function isCurrentUserMessage(array $message): bool
    {
        $userId = $message['user']['id'] ?? '';

        if ($this->isDemo) {
            return $userId === 'user_current' || $userId === 'student_current';
        }

        return str_starts_with($userId, 'user_' . auth()->id());
    }

    public function render()
    {
        return view('livewire.chat.provider-chat-window', [
            'streamConfig' => $this->streamConfig,
        ]);
    }
}
