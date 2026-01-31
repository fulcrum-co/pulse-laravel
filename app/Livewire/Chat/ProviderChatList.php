<?php

namespace App\Livewire\Chat;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\StreamChatService;
use Illuminate\Support\Collection;
use Livewire\Component;

class ProviderChatList extends Component
{
    public string $search = '';
    public ?int $selectedConversationId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(?int $conversationId = null): void
    {
        $this->selectedConversationId = $conversationId;
    }

    /**
     * Get conversations for the current user.
     */
    public function getConversationsProperty(): Collection
    {
        $user = auth()->user();

        $query = ProviderConversation::query()
            ->with(['provider', 'student'])
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->active()
            ->orderByDesc('last_message_at');

        if ($this->search) {
            $query->whereHas('provider', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get unread count for the current user.
     */
    public function getUnreadCountProperty(): int
    {
        $user = auth()->user();

        return ProviderConversation::query()
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->active()
            ->withUnreadForInitiator()
            ->count();
    }

    /**
     * Get all available providers for starting new conversations.
     */
    public function getAvailableProvidersProperty(): Collection
    {
        $user = auth()->user();

        // Get providers not already in conversation with this user
        $existingProviderIds = ProviderConversation::query()
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->active()
            ->pluck('provider_id');

        return Provider::query()
            ->active()
            ->whereNotIn('id', $existingProviderIds)
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * Select a conversation.
     */
    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;

        // Mark as read
        $conversation = ProviderConversation::find($conversationId);
        if ($conversation) {
            $conversation->markReadByInitiator();
        }

        // Emit event for the chat window
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    /**
     * Start a new conversation with a provider.
     */
    public function startConversation(int $providerId): void
    {
        $user = auth()->user();
        $provider = Provider::findOrFail($providerId);

        // Check if conversation already exists
        $existing = ProviderConversation::query()
            ->where('provider_id', $providerId)
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->first();

        if ($existing) {
            $this->selectConversation($existing->id);
            return;
        }

        // Create new conversation
        $streamService = app(StreamChatService::class);

        try {
            // Create GetStream channel
            $channelData = $streamService->isConfigured()
                ? $streamService->createProviderChannel($provider, $user)
                : ['channel_id' => $streamService->generateChannelId($provider, $user)];

            // Create local conversation record
            $conversation = ProviderConversation::create([
                'provider_id' => $provider->id,
                'initiator_type' => get_class($user),
                'initiator_id' => $user->id,
                'stream_channel_id' => $channelData['channel_id'],
                'stream_channel_type' => 'messaging',
                'status' => ProviderConversation::STATUS_ACTIVE,
            ]);

            $this->selectConversation($conversation->id);

            session()->flash('message', "Started conversation with {$provider->name}");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start conversation. Please try again.');
        }
    }

    /**
     * Archive a conversation.
     */
    public function archiveConversation(int $conversationId): void
    {
        $conversation = ProviderConversation::find($conversationId);

        if ($conversation && $conversation->initiator_id === auth()->id()) {
            $conversation->archive();

            if ($this->selectedConversationId === $conversationId) {
                $this->selectedConversationId = null;
            }

            session()->flash('message', 'Conversation archived');
        }
    }

    public function render()
    {
        return view('livewire.chat.provider-chat-list', [
            'conversations' => $this->conversations,
            'unreadCount' => $this->unreadCount,
            'availableProviders' => $this->availableProviders,
        ])->layout('components.layouts.dashboard', [
            'title' => 'Messages',
        ]);
    }
}
