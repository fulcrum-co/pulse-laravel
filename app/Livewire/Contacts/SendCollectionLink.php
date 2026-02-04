<?php

namespace App\Livewire\Contacts;

use App\Models\CollectionEvent;
use App\Services\CollectionTokenService;
use App\Services\SinchService;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class SendCollectionLink extends Component
{
    public $contact;

    public array $events = [];

    public ?int $eventId = null;

    public function mount($contact): void
    {
        $this->contact = $contact;
        if (! Schema::hasTable('collection_events')) {
            $this->events = [];
            $this->eventId = null;
            return;
        }

        $this->events = CollectionEvent::where('organization_id', $contact->org_id)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->toArray();
        $this->eventId = $this->events[0]['id'] ?? null;
    }

    public function send(CollectionTokenService $tokenService, SinchService $sinchService): void
    {
        $this->validate([
            'eventId' => 'required|integer',
        ]);

        $event = CollectionEvent::where('organization_id', $this->contact->org_id)
            ->findOrFail($this->eventId);

        $phone = $this->contact->user?->phone;
        if (! $phone) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No phone number available for this contact.',
            ]);
            return;
        }

        $link = $tokenService->createSignedLink($event, $this->contact);
        $sinchService->sendSms($phone, "Please complete this update: {$link}");

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Collection link sent via SMS.',
        ]);
    }

    public function render()
    {
        return view('livewire.contacts.send-collection-link');
    }
}
