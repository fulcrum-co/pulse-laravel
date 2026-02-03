<?php

namespace App\Livewire\Chat;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\DemoConversationService;
use App\Services\StreamChatService;
use Livewire\Attributes\On;
use Livewire\Component;

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

    // Booking modal state
    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    public string $bookingType = 'session';

    public string $locationType = 'remote';

    public string $bookingNotes = '';

    public int $currentMonth;

    public int $currentYear;

    protected StreamChatService $streamService;

    public function boot(StreamChatService $streamService): void
    {
        $this->streamService = $streamService;
    }

    public function mount(?string $conversationId = null, bool $isDemo = true): void
    {
        $this->isDemo = $isDemo;
        $this->currentMonth = (int) now()->format('n');
        $this->currentYear = (int) now()->format('Y');

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

        $this->conversation = ProviderConversation::with(['provider', 'participant'])
            ->find($conversationId);

        if (! $this->conversation) {
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
        if (! empty($conversations)) {
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
        if (! $this->conversation || ! $this->streamService->isConfigured()) {
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
     * Archive a conversation.
     */
    public function archiveConversation(string $conversationId): void
    {
        if ($this->isDemo || str_starts_with($conversationId, 'conv_')) {
            if ($this->conversation && $this->conversation->id === $conversationId) {
                $this->conversation = null;
                $this->messages = [];
            }

            $this->dispatch('conversation-archived', id: $conversationId);

            return;
        }

        $conversation = ProviderConversation::find($conversationId);

        if (! $conversation) {
            return;
        }

        $conversation->archive();

        if ($this->conversation && $this->conversation->id === $conversationId) {
            $this->conversation = null;
            $this->messages = [];
        }

        $this->dispatch('conversation-archived', id: $conversationId);
    }

    /**
     * Send a message.
     */
    public function sendMessage(): void
    {
        if (empty(trim($this->messageText))) {
            return;
        }

        if (! $this->conversation) {
            return;
        }

        $this->isLoading = true;
        $user = auth()->user();
        $messageContent = trim($this->messageText);

        // For demo mode, add message to local array
        if ($this->isDemo) {
            $this->messages[] = [
                'id' => 'msg_new_'.uniqid(),
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

        // For demo mode, auto-connect after a brief delay via JS
        $this->dispatch('video-call-started', autoConnect: $this->isDemo);
    }

    /**
     * Simulate connecting to call (for demo).
     */
    public function connectCall(): void
    {
        $this->videoCallState = 'connected';
        $this->dispatch('video-call-connected');
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
                'id' => 'msg_call_'.uniqid(),
                'text' => '📹 Video call ended • Duration: 0:'.rand(30, 59),
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
        if (! $this->streamService->isConfigured()) {
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
        if ($this->isDemo || ! $this->streamService->isConfigured()) {
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
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->bookingType = 'session';
        $this->locationType = 'remote';
        $this->bookingNotes = '';
        $this->currentMonth = (int) now()->format('n');
        $this->currentYear = (int) now()->format('Y');
    }

    /**
     * Close booking modal.
     */
    public function closeBookingModal(): void
    {
        $this->showBookingModal = false;
    }

    /**
     * Navigate to previous month in calendar.
     */
    public function previousMonth(): void
    {
        $this->currentMonth--;
        if ($this->currentMonth < 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        }
    }

    /**
     * Navigate to next month in calendar.
     */
    public function nextMonth(): void
    {
        $this->currentMonth++;
        if ($this->currentMonth > 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        }
    }

    /**
     * Select a date in the booking calendar.
     */
    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = null; // Reset time when date changes
    }

    /**
     * Select a time slot.
     */
    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    /**
     * Get calendar days for the current month.
     */
    public function getCalendarDaysProperty(): array
    {
        $firstDay = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startPadding = $firstDay->dayOfWeek; // 0 = Sunday

        $days = [];

        // Add empty slots for days before the 1st
        for ($i = 0; $i < $startPadding; $i++) {
            $days[] = null;
        }

        // Add all days of the month
        for ($day = 1; $day <= $lastDay->day; $day++) {
            $date = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, $day);
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $day,
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast() && ! $date->isToday(),
                'isWeekend' => $date->isWeekend(),
                'isAvailable' => ! $date->isPast() && ! $date->isWeekend(),
            ];
        }

        return $days;
    }

    /**
     * Get available time slots for the selected date.
     */
    public function getAvailableTimesProperty(): array
    {
        if (! $this->selectedDate) {
            return [];
        }

        // Demo: Return fixed time slots (in production, this would query provider availability)
        return [
            '9:00am',
            '10:00am',
            '11:00am',
            '12:30pm',
            '2:00pm',
            '2:30pm',
            '3:00pm',
            '3:30pm',
            '4:00pm',
            '4:30pm',
        ];
    }

    /**
     * Get formatted current month name.
     */
    public function getMonthNameProperty(): string
    {
        return \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    /**
     * Confirm booking.
     */
    public function confirmBooking(): void
    {
        if (! $this->selectedDate || ! $this->selectedTime) {
            return;
        }

        // In demo mode, just show success message
        if ($this->isDemo) {
            $this->messages[] = [
                'id' => 'msg_booking_'.uniqid(),
                'text' => "📅 Booking request sent for {$this->selectedDate} at {$this->selectedTime}",
                'user' => ['id' => 'system', 'name' => 'System'],
                'created_at' => now()->toIso8601String(),
                'is_system' => true,
            ];
            $this->closeBookingModal();
            session()->flash('message', 'Booking request sent successfully!');

            return;
        }

        // TODO: Implement real booking logic
        $this->closeBookingModal();
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
            return 'Yesterday '.$date->format('g:i A');
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
            return $userId === 'user_current' || $userId === 'learner_current';
        }

        return str_starts_with($userId, 'user_'.auth()->id());
    }

    public function render()
    {
        return view('livewire.chat.provider-chat-window', [
            'streamConfig' => $this->streamConfig,
        ]);
    }
}
