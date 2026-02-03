<?php

namespace App\Livewire\Survey;

use App\Models\Participant;
use App\Models\Survey;
use App\Models\User;
use App\Services\SurveyDeliveryService;
use Illuminate\Support\Collection;
use Livewire\Component;

class DeliveryManager extends Component
{
    public Survey $survey;

    // Delivery Configuration
    public string $channel = 'web';

    public string $recipientType = 'participant';

    public array $selectedRecipients = [];

    public string $phoneNumber = '';

    public ?string $scheduledFor = null;

    public bool $scheduleDelivery = false;

    // Search/Filter
    public string $search = '';

    // UI State
    public bool $showRecipientPicker = false;

    public bool $showPhoneInput = false;

    public bool $isDelivering = false;

    public ?array $deliveryResult = null;

    protected SurveyDeliveryService $deliveryService;

    protected $rules = [
        'channel' => 'required|string|in:web,sms,voice_call,whatsapp',
        'selectedRecipients' => 'required|array|min:1',
        'phoneNumber' => 'required_if:channel,sms,voice_call,whatsapp',
        'scheduledFor' => 'nullable|date|after:now',
    ];

    public function boot(SurveyDeliveryService $deliveryService): void
    {
        $this->deliveryService = $deliveryService;
    }

    public function mount(Survey $survey): void
    {
        $this->survey = $survey;
        $this->channel = $survey->delivery_channels[0] ?? 'web';
    }

    public function updatedChannel(): void
    {
        $this->showPhoneInput = in_array($this->channel, ['sms', 'voice_call', 'whatsapp']);
    }

    public function selectRecipient(int $id, string $type, ?string $phone = null): void
    {
        $key = "{$type}_{$id}";

        if (isset($this->selectedRecipients[$key])) {
            unset($this->selectedRecipients[$key]);
        } else {
            $this->selectedRecipients[$key] = [
                'type' => $type,
                'id' => $id,
                'phone_number' => $phone,
            ];
        }
    }

    public function selectAll(): void
    {
        foreach ($this->getAvailableRecipients() as $recipient) {
            $key = "{$recipient['type']}_{$recipient['id']}";
            $this->selectedRecipients[$key] = [
                'type' => $recipient['type'],
                'id' => $recipient['id'],
                'phone_number' => $recipient['phone'] ?? null,
            ];
        }
    }

    public function clearSelection(): void
    {
        $this->selectedRecipients = [];
    }

    public function addManualRecipient(): void
    {
        if (empty($this->phoneNumber)) {
            return;
        }

        $key = 'manual_'.md5($this->phoneNumber);
        $this->selectedRecipients[$key] = [
            'type' => 'manual',
            'id' => 0,
            'phone_number' => $this->phoneNumber,
        ];

        $this->phoneNumber = '';
    }

    public function deliver(): void
    {
        $this->validate([
            'channel' => 'required|string|in:web,sms,voice_call,whatsapp',
            'selectedRecipients' => 'required|array|min:1',
        ]);

        $this->isDelivering = true;
        $results = [
            'success' => 0,
            'failed' => 0,
            'deliveries' => [],
        ];

        foreach ($this->selectedRecipients as $recipient) {
            try {
                $delivery = $this->deliveryService->deliver(
                    $this->survey,
                    $this->channel,
                    $recipient['type'],
                    $recipient['id'],
                    $recipient['phone_number'],
                    $this->scheduleDelivery ? $this->scheduledFor : null
                );

                $results['success']++;
                $results['deliveries'][] = [
                    'id' => $delivery->id,
                    'status' => $delivery->status,
                    'recipient' => $recipient,
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['deliveries'][] = [
                    'error' => $e->getMessage(),
                    'recipient' => $recipient,
                ];
            }
        }

        $this->deliveryResult = $results;
        $this->isDelivering = false;

        if ($results['success'] > 0) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Successfully sent {$results['success']} survey(s).",
            ]);
        }

        if ($results['failed'] > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "{$results['failed']} delivery(ies) failed.",
            ]);
        }

        // Clear selection after delivery
        $this->selectedRecipients = [];
    }

    public function getAvailableRecipients(): array
    {
        $user = auth()->user();
        $recipients = [];

        if ($this->recipientType === 'participant') {
            $query = Participant::where('org_id', $user->org_id);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            }

            $participants = $query->limit(50)->get();

            foreach ($participants as $participant) {
                $recipients[] = [
                    'type' => 'participant',
                    'id' => $participant->id,
                    'name' => $participant->full_name,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'level' => $participant->level,
                ];
            }
        } else {
            $query = User::where('org_id', $user->org_id);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            }

            $users = $query->limit(50)->get();

            foreach ($users as $u) {
                $recipients[] = [
                    'type' => 'user',
                    'id' => $u->id,
                    'name' => $u->first_name.' '.$u->last_name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'role' => $u->primary_role,
                ];
            }
        }

        return $recipients;
    }

    public function getRecentDeliveriesProperty(): Collection
    {
        return $this->survey->deliveries()
            ->with('attempt')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getDeliveryStatsProperty(): array
    {
        return [
            'total' => $this->survey->deliveries()->count(),
            'pending' => $this->survey->deliveries()->where('status', 'pending')->count(),
            'delivered' => $this->survey->deliveries()->where('status', 'delivered')->count(),
            'completed' => $this->survey->deliveries()->where('status', 'completed')->count(),
            'failed' => $this->survey->deliveries()->where('status', 'failed')->count(),
        ];
    }

    public function getChannelOptionsProperty(): array
    {
        return [
            'web' => ['label' => 'Web Link', 'icon' => 'globe-alt', 'description' => 'Send a link via email or copy to share'],
            'sms' => ['label' => 'SMS', 'icon' => 'chat-bubble-left', 'description' => 'Send survey via text message'],
            'voice_call' => ['label' => 'Voice Call', 'icon' => 'phone', 'description' => 'Call and read questions via TTS'],
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'chat-bubble-oval-left', 'description' => 'Send via WhatsApp message'],
        ];
    }

    public function render()
    {
        return view('livewire.survey.delivery-manager', [
            'recipients' => $this->getAvailableRecipients(),
        ]);
    }
}
