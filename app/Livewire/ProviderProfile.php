<?php

namespace App\Livewire;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Services\StreamChatService;
use Livewire\Component;

class ProviderProfile extends Component
{
    public Provider $provider;

    public function mount(Provider $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * Start or open a conversation with this provider.
     */
    public function messageProvider(): void
    {
        $user = auth()->user();

        // Check if conversation already exists
        $existing = ProviderConversation::query()
            ->where('provider_id', $this->provider->id)
            ->where('initiator_type', get_class($user))
            ->where('initiator_id', $user->id)
            ->first();

        if ($existing) {
            // Redirect to existing conversation
            $this->redirect(route('messages.index') . '?conversation=' . $existing->id);
            return;
        }

        // Create new conversation
        $streamService = app(StreamChatService::class);

        try {
            $channelId = $streamService->generateChannelId($this->provider, $user);

            // Try to create GetStream channel if configured
            if ($streamService->isConfigured()) {
                $streamService->createProviderChannel($this->provider, $user);
            }

            // Create local conversation record
            $conversation = ProviderConversation::create([
                'provider_id' => $this->provider->id,
                'initiator_type' => get_class($user),
                'initiator_id' => $user->id,
                'stream_channel_id' => $channelId,
                'stream_channel_type' => 'messaging',
                'status' => ProviderConversation::STATUS_ACTIVE,
            ]);

            $this->redirect(route('messages.index') . '?conversation=' . $conversation->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start conversation. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.provider-profile')
            ->layout('layouts.dashboard', ['title' => 'Provider Profile']);
    }
}
