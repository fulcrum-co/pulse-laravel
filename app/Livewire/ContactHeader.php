<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\User;
use Livewire\Component;

class ContactHeader extends Component
{
    public $contact;

    public string $contactType;

    public function mount($contact)
    {
        $this->contact = $contact;
        $this->contactType = $contact instanceof Student ? 'student' : 'user';
    }

    public function getDisplayNameProperty()
    {
        if ($this->contact instanceof Student) {
            return $this->contact->full_name ?? $this->contact->first_name.' '.$this->contact->last_name;
        }

        return $this->contact->name ?? $this->contact->first_name.' '.$this->contact->last_name;
    }

    public function getAvatarUrlProperty()
    {
        // For students, avatar is on the related User model
        if ($this->contact instanceof Student && $this->contact->user?->avatar_url) {
            return $this->contact->user->avatar_url;
        }

        // For users (teachers, parents), check directly
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
        if ($this->contact instanceof Student) {
            return 'Student';
        }

        if (method_exists($this->contact, 'getRoleNames')) {
            $roles = $this->contact->getRoleNames();
            if ($roles->isNotEmpty()) {
                return $roles->map(fn ($role) => ucfirst($role))->join(', ');
            }
        }

        return 'User';
    }

    public function getContactInfoProperty()
    {
        $info = [];

        if ($this->contact instanceof Student) {
            if ($this->contact->grade_level) {
                $info[] = ['label' => 'Grade', 'value' => $this->contact->grade_level];
            }
            if ($this->contact->student_number) {
                $info[] = ['label' => 'ID', 'value' => $this->contact->student_number];
            }
            // For students, email/phone are on the User model
            $user = $this->contact->user;
            if ($user?->email) {
                $info[] = ['label' => 'Email', 'value' => $user->email];
            }
            if ($user?->phone) {
                $info[] = ['label' => 'Phone', 'value' => $user->phone];
            }
        } else {
            // For users (teachers, parents), check directly
            if ($this->contact->email) {
                $info[] = ['label' => 'Email', 'value' => $this->contact->email];
            }
            if ($this->contact->phone) {
                $info[] = ['label' => 'Phone', 'value' => $this->contact->phone];
            }
        }

        return $info;
    }

    public function getRiskLevelProperty()
    {
        if ($this->contact instanceof Student) {
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
