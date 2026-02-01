<?php

namespace App\Livewire\Chat;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\DemoConversationService;
use App\Services\StreamChatService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ProviderChatList extends Component
{
    public string $search = '';
    public ?string $selectedConversationId = null;
    public bool $useDemoData = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'conversation' => ['except' => '', 'as' => 'conversation'],
    ];

    public ?string $conversation = null;

    public function mount(?string $conversationId = null): void
    {
        // Handle conversation from query string or parameter
        $this->selectedConversationId = $conversationId ?? $this->conversation ?? request()->query('conversation');

        // Check if we have real conversations or should use demo data
        $user = auth()->user();
        if ($user) {
            try {
                if (!Schema::hasTable('provider_conversations')) {
                    $this->useDemoData = true;
                } else {
                    $realConversations = ProviderConversation::query()
                        ->where('initiator_type', get_class($user))
                        ->where('initiator_id', $user->id)
                        ->count();
                    $this->useDemoData = $realConversations === 0;
                }
            } catch (\Exception $e) {
                $this->useDemoData = true;
            }
        }

        // If a conversation was specified and it's a real one, use real data mode
        if ($this->selectedConversationId && is_numeric($this->selectedConversationId)) {
            $this->useDemoData = false;
        }
    }

    /**
     * Get conversations for the current user.
     */
    public function getConversationsProperty(): Collection
    {
        // Use demo data if no real conversations exist
        if ($this->useDemoData) {
            return $this->getDemoConversations();
        }

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
     * Get demo conversations.
     */
    protected function getDemoConversations(): Collection
    {
        $conversations = DemoConversationService::getConversations();

        $demoConversations = array_map(
            fn($conv) => DemoConversationService::createDemoConversation($conv),
            $conversations
        );

        // Filter by search if needed
        if ($this->search) {
            $search = strtolower($this->search);
            $demoConversations = array_filter($demoConversations, function ($conv) use ($search) {
                return str_contains(strtolower($conv->provider->name), $search);
            });
        }

        return collect($demoConversations);
    }

    /**
     * Get unread count for the current user.
     */
    public function getUnreadCountProperty(): int
    {
        if ($this->useDemoData) {
            return collect($this->conversations)->sum('unread_count_initiator');
        }

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
        if ($this->useDemoData) {
            $providers = DemoConversationService::getAvailableProviders();
            return collect(array_map(
                fn($p) => DemoConversationService::createDemoProvider($p),
                $providers
            ));
        }

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
    public function selectConversation(string $conversationId): void
    {
        $this->selectedConversationId = $conversationId;

        // Mark as read for real conversations
        if (!$this->useDemoData && is_numeric($conversationId)) {
            $conversation = ProviderConversation::find($conversationId);
            if ($conversation) {
                $conversation->markReadByInitiator();
            }
        }

        // Emit event for the chat window
        $this->dispatch('conversation-selected', conversationId: $conversationId, isDemo: $this->useDemoData);
    }

    /**
     * Start a new conversation with a provider.
     */
    public function startConversation(string $providerId): void
    {
        // For demo providers, just select a demo conversation
        if (str_starts_with($providerId, 'demo_')) {
            $conversations = $this->conversations;
            if ($conversations->isNotEmpty()) {
                $this->selectConversation($conversations->first()->id);
            }
            return;
        }

        $user = auth()->user();
        $provider = Provider::findOrFail($providerId);

        // Check if conversation already exists
        $existing = ProviderConversation::query()
            ->where('provider_id', $providerId)
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->first();

        if ($existing) {
            $this->selectConversation((string) $existing->id);
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

            $this->useDemoData = false;
            $this->selectConversation((string) $conversation->id);

            session()->flash('message', "Started conversation with {$provider->name}");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start conversation. Please try again.');
        }
    }

    /**
     * Archive a conversation.
     */
    public function archiveConversation(string $conversationId): void
    {
        if ($this->useDemoData) {
            session()->flash('message', 'Demo conversations cannot be archived');
            return;
        }

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
