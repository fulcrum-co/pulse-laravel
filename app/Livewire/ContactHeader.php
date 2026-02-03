<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\User;
use Livewire\Component;

class ContactHeader extends Component
{
    public $contact;

    public string $contactType;

    public function mount($contact)
    {
        $this->contact = $contact;
        $this->contactType = $contact instanceof Participant ? 'participant' : 'user';
    }

    public function getDisplayNameProperty()
    {
        if ($this->contact instanceof Participant) {
            return $this->contact->full_name ?? $this->contact->first_name.' '.$this->contact->last_name;
        }

        return $this->contact->name ?? $this->contact->first_name.' '.$this->contact->last_name;
    }

    public function getAvatarUrlProperty()
    {
        // For participants, avatar is on the related User model
        if ($this->contact instanceof Participant && $this->contact->user?->avatar_url) {
            return $this->contact->user->avatar_url;
        }

        // For users (instructors, direct_supervisors), check directly
        if ($this->contact->avatar_url) {
            return $this->contact->avatar_url;
        }

        // Generate initials-based placeholder
        $name = $this->displayName;
        $initials = collect(explode(' ', $name))
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->join('');

        return 'https://ui-avatars.com/api/?name='.urlencode($initials).'&background=3b82f6&color=fff&size=128';
    }

    public function getRoleDisplayProperty()
    {
        $terminology = app(\App\Services\TerminologyService::class);

        if ($this->contact instanceof Participant) {
            return $terminology->get('participant_label');
        }

        if (method_exists($this->contact, 'getRoleNames')) {
            $roles = $this->contact->getRoleNames();
            if ($roles->isNotEmpty()) {
                return $roles->map(fn ($role) => ucfirst($role))->join(', ');
            }
        }

        return $terminology->get('user_label');
    }

    public function getContactInfoProperty()
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $info = [];

        if ($this->contact instanceof Participant) {
            if ($this->contact->level) {
                $info[] = ['label' => $terminology->get('level_label'), 'value' => $this->contact->level];
            }
            if ($this->contact->participant_number) {
                $info[] = ['label' => $terminology->get('id_label'), 'value' => $this->contact->participant_number];
            }
            // For participants, email/phone are on the User model
            $user = $this->contact->user;
            if ($user?->email) {
                $info[] = ['label' => $terminology->get('email_label'), 'value' => $user->email];
            }
            if ($user?->phone) {
                $info[] = ['label' => $terminology->get('phone_label'), 'value' => $user->phone];
            }
        } else {
            // For users (instructors, direct_supervisors), check directly
            if ($this->contact->email) {
                $info[] = ['label' => $terminology->get('email_label'), 'value' => $this->contact->email];
            }
            if ($this->contact->phone) {
                $info[] = ['label' => $terminology->get('phone_label'), 'value' => $this->contact->phone];
            }
        }

        return $info;
    }

    public function getRiskLevelProperty()
    {
        if ($this->contact instanceof Participant) {
            return $this->contact->risk_level;
        }

        return null;
    }

    public function getRiskColorProperty()
    {
        return match ($this->riskLevel) {
            'high' => 'bg-red-100 text-red-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function render()
    {
        return view('livewire.contact-header', [
            'displayName' => $this->displayName,
            'avatarUrl' => $this->avatarUrl,
            'roleDisplay' => $this->roleDisplay,
            'contactInfo' => $this->contactInfo,
            'riskLevel' => $this->riskLevel,
            'riskColor' => $this->riskColor,
        ]);
    }
}
