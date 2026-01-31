<?php

namespace App\Livewire\Chat;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\StreamChatService;
use Livewire\Component;
use Livewire\Attributes\On;

class ProviderChatWindow extends Component
{
    public ?ProviderConversation $conversation = null;
    public string $messageText = '';
    public array $messages = [];
    public bool $isLoading = false;
    public bool $showBookingModal = false;

    protected StreamChatService $streamService;

    public function boot(StreamChatService $streamService): void
    {
        $this->streamService = $streamService;
    }

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->loadConversation($conversationId);
        }
    }

    #[On('conversation-selected')]
    public function onConversationSelected(int $conversationId): void
    {
        $this->loadConversation($conversationId);
    }

    /**
     * Load a conversation and its messages.
     */
    public function loadConversation(int $conversationId): void
    {
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

            // Dispatch notification job (in production)
            // SendProviderNotificationJob::dispatch($this->conversation, ['text' => $messageContent]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        }

        $this->isLoading = false;
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
        if (!$this->streamService->isConfigured()) {
            return ['configured' => false];
        }

        $user = auth()->user();

        return [
            'configured' => true,
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
        $this->dispatch('open-booking-modal', providerId: $this->conversation->provider_id);
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

    public function render()
    {
        return view('livewire.chat.provider-chat-window', [
            'streamConfig' => $this->streamConfig,
        ]);
    }
}
