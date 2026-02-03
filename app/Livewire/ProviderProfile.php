<?php

namespace App\Livewire;

use App\Models\ContactList;
use App\Models\Provider;
use App\Models\ProviderAssignment;
use App\Models\ProviderConversation;
use App\Models\Learner;
use App\Services\StreamChatService;
use Livewire\Component;

class ProviderProfile extends Component
{
    public Provider $provider;

    // Assign modal state
    public bool $showAssignModal = false;

    public string $assignType = 'learner'; // learner or list

    public ?int $selectedLearnerId = null;

    public ?int $selectedListId = null;

    public string $assignNote = '';

    public function mount(Provider $provider): void
    {
        // Ensure the user has access to this provider's organization
        if (! auth()->user()->canAccessOrganization($provider->org_id)) {
            abort(403);
        }

        $this->provider = $provider;
    }

    /**
     * Check if the current user can push content to downstream organizations.
     */
    public function getCanPushProperty(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        return $hasDownstream || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Open the push modal for this provider.
     */
    public function openPushModal(): void
    {
        $this->dispatch('openPushProvider', $this->provider->id);
    }

    /**
     * Open the assign modal.
     */
    public function openAssignModal(): void
    {
        $this->resetAssignForm();
        $this->showAssignModal = true;
    }

    /**
     * Close the assign modal.
     */
    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->resetAssignForm();
    }

    /**
     * Reset the assign form.
     */
    protected function resetAssignForm(): void
    {
        $this->assignType = 'learner';
        $this->selectedLearnerId = null;
        $this->selectedListId = null;
        $this->assignNote = '';
    }

    /**
     * Assign this provider to a learner or list.
     */
    public function assignProvider(): void
    {
        $user = auth()->user();

        if ($this->assignType === 'learner') {
            $this->validate([
                'selectedLearnerId' => 'required|exists:learners,id',
            ]);

            // Check for existing assignment
            $exists = ProviderAssignment::where('provider_id', $this->provider->id)
                ->where('learner_id', $this->selectedLearnerId)
                ->whereIn('status', ['assigned', 'active'])
                ->exists();

            if ($exists) {
                session()->flash('error', 'This provider is already assigned to this learner.');

                return;
            }

            ProviderAssignment::create([
                'provider_id' => $this->provider->id,
                'learner_id' => $this->selectedLearnerId,
                'assigned_by' => $user->id,
                'notes' => $this->assignNote ?: null,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            session()->flash('success', 'Provider assigned to learner successfully.');

        } elseif ($this->assignType === 'list') {
            $this->validate([
                'selectedListId' => 'required|exists:contact_lists,id',
            ]);

            $list = ContactList::find($this->selectedListId);
            $learners = $list->learners;
            $count = 0;

            foreach ($learners as $learner) {
                // Avoid duplicate assignments
                $exists = ProviderAssignment::where('provider_id', $this->provider->id)
                    ->where('learner_id', $learner->id)
                    ->whereIn('status', ['assigned', 'active'])
                    ->exists();

                if (! $exists) {
                    ProviderAssignment::create([
                        'provider_id' => $this->provider->id,
                        'learner_id' => $learner->id,
                        'assigned_by' => $user->id,
                        'notes' => $this->assignNote ?: null,
                        'assigned_at' => now(),
                        'status' => 'assigned',
                    ]);
                    $count++;
                }
            }

            session()->flash('success', "Provider assigned to {$count} learners from the list.");
        }

        $this->closeAssignModal();
    }

    /**
     * Get accessible learners for assignment.
     */
    public function getLearnersProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return Learner::whereIn('org_id', $accessibleOrgIds)
            ->with('user')
            ->get()
            ->sortBy(fn ($learner) => $learner->user?->name ?? '')
            ->values();
    }

    /**
     * Get contact lists for assignment.
     */
    public function getContactListsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return ContactList::whereIn('org_id', $accessibleOrgIds)
            ->whereIn('list_type', ['learner', 'mixed'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get assignment count for this provider.
     */
    public function getAssignmentCountProperty(): int
    {
        return $this->provider->assignments()->count();
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
            $this->redirect(route('messages.index').'?conversation='.$existing->id);

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

            $this->redirect(route('messages.index').'?conversation='.$conversation->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start conversation. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.provider-profile', [
            'canPush' => $this->canPush,
            'learners' => $this->learners,
            'contactLists' => $this->contactLists,
            'assignmentCount' => $this->assignmentCount,
        ])->layout('layouts.dashboard', ['title' => $this->provider->name]);
    }
}
