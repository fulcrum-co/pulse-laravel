<?php

namespace App\Livewire\Auth;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PublicRegister extends Component
{
    public ?Organization $organization = null;
    public ?int $orgId = null;

    // Form fields
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public bool $agreeToTerms = false;

    public bool $registrationSuccess = false;

    protected $rules = [
        'firstName' => 'required|string|max:100',
        'lastName' => 'required|string|max:100',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'passwordConfirmation' => 'required|same:password',
        'agreeToTerms' => 'accepted',
    ];

    protected $messages = [
        'agreeToTerms.accepted' => 'You must agree to the terms and conditions.',
        'passwordConfirmation.same' => 'The passwords do not match.',
    ];

    public function mount(?string $org = null): void
    {
        // Load organization if provided
        if ($org) {
            $this->organization = Organization::where('id', $org)
                ->orWhere('org_name', 'like', str_replace('-', '%', $org) . '%')
                ->first();

            if ($this->organization) {
                $this->orgId = $this->organization->id;
            }
        }
    }

    public function register(): void
    {
        $this->validate();

        // Create the user as a "viewer" role
        $user = User::create([
            'org_id' => $this->orgId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'primary_role' => 'viewer',
            'active' => true,
            'suspended' => false,
        ]);

        // Log the user in
        Auth::login($user);

        // Redirect to public hub or dashboard
        if ($this->organization) {
            $this->redirect(route('public.resources', ['orgSlug' => $this->organization->id]));
        } else {
            $this->redirect(route('dashboard'));
        }
    }

    public function render()
    {
        return view('livewire.auth.public-register')
            ->layout('layouts.public', [
                'title' => 'Create Account' . ($this->organization ? ' - ' . $this->organization->org_name : ''),
                'orgName' => $this->organization?->org_name,
                'orgLogo' => $this->organization?->logo_url,
            ]);
    }
}
