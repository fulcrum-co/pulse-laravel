<?php

namespace App\Livewire;

use App\Models\ContactList;
use App\Models\Provider;
use App\Models\ProviderAssignment;
use App\Models\ProviderConversation;
use App\Models\Student;
use App\Services\StreamChatService;
use Livewire\Component;

class ProviderProfile extends Component
{
    public Provider $provider;

    // Assign modal state
    public bool $showAssignModal = false;

    public string $assignType = 'student'; // student or list

    public ?int $selectedStudentId = null;

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
        $this->assignType = 'student';
        $this->selectedStudentId = null;
        $this->selectedListId = null;
        $this->assignNote = '';
    }

    /**
     * Assign this provider to a student or list.
     */
    public function assignProvider(): void
    {
        $user = auth()->user();

        if ($this->assignType === 'student') {
            $this->validate([
                'selectedStudentId' => 'required|exists:students,id',
            ]);

            // Check for existing assignment
            $exists = ProviderAssignment::where('provider_id', $this->provider->id)
                ->where('student_id', $this->selectedStudentId)
                ->whereIn('status', ['assigned', 'active'])
                ->exists();

            if ($exists) {
                session()->flash('error', 'This provider is already assigned to this student.');

                return;
            }

            ProviderAssignment::create([
                'provider_id' => $this->provider->id,
                'student_id' => $this->selectedStudentId,
                'assigned_by' => $user->id,
                'notes' => $this->assignNote ?: null,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            session()->flash('success', 'Provider assigned to student successfully.');

        } elseif ($this->assignType === 'list') {
            $this->validate([
                'selectedListId' => 'required|exists:contact_lists,id',
            ]);

            $list = ContactList::find($this->selectedListId);
            $students = $list->students;
            $count = 0;

            foreach ($students as $student) {
                // Avoid duplicate assignments
                $exists = ProviderAssignment::where('provider_id', $this->provider->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['assigned', 'active'])
                    ->exists();

                if (! $exists) {
                    ProviderAssignment::create([
                        'provider_id' => $this->provider->id,
                        'student_id' => $student->id,
                        'assigned_by' => $user->id,
                        'notes' => $this->assignNote ?: null,
                        'assigned_at' => now(),
                        'status' => 'assigned',
                    ]);
                    $count++;
                }
            }

            session()->flash('success', "Provider assigned to {$count} students from the list.");
        }

        $this->closeAssignModal();
    }

    /**
     * Get accessible students for assignment.
     */
    public function getStudentsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return Student::whereIn('org_id', $accessibleOrgIds)
            ->with('user')
            ->get()
            ->sortBy(fn ($student) => $student->user?->name ?? '')
            ->values();
    }

    /**
     * Get contact lists for assignment.
     */
    public function getContactListsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return ContactList::whereIn('org_id', $accessibleOrgIds)
            ->whereIn('list_type', ['student', 'mixed'])
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
            'students' => $this->students,
            'contactLists' => $this->contactLists,
            'assignmentCount' => $this->assignmentCount,
        ])->layout('layouts.dashboard', ['title' => $this->provider->name]);
    }
}
