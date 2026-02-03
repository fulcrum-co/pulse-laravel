<?php

namespace App\Livewire;

use Livewire\Component;

class DemoRoleSwitcher extends Component
{
    public string $currentRole = '';

    public bool $isOpen = false;

    public array $availableRoles = [];

    public function mount(): void
    {
        $terminology = app(\App\Services\TerminologyService::class);

        $this->currentRole = session('demo_role_override', 'actual');
        $this->availableRoles = [
            'actual' => [
                'label' => $terminology->get('demo_role_actual_label'),
                'icon' => 'user-circle',
                'description' => $terminology->get('demo_role_actual_description'),
            ],
            'consultant' => [
                'label' => $terminology->get('demo_role_consultant_label'),
                'icon' => 'academic-cap',
                'description' => $terminology->get('demo_role_consultant_description'),
            ],
            'administrative_role' => [
                'label' => $terminology->get('demo_role_administrative_label'),
                'icon' => 'building-library',
                'description' => $terminology->get('demo_role_administrative_description'),
            ],
            'organization_admin' => [
                'label' => $terminology->get('demo_role_organization_admin_label'),
                'icon' => 'building-office-2',
                'description' => $terminology->get('demo_role_organization_admin_description'),
            ],
            'support_person' => [
                'label' => $terminology->get('demo_role_support_person_label'),
                'icon' => 'heart',
                'description' => $terminology->get('demo_role_support_person_description'),
            ],
            'instructor' => [
                'label' => $terminology->get('demo_role_instructor_label'),
                'icon' => 'user-group',
                'description' => $terminology->get('demo_role_instructor_description'),
            ],
            'participant' => [
                'label' => $terminology->get('demo_role_participant_label'),
                'icon' => 'face-smile',
                'description' => $terminology->get('demo_role_participant_description'),
            ],
            'direct_supervisor' => [
                'label' => $terminology->get('demo_role_direct_supervisor_label'),
                'icon' => 'home',
                'description' => $terminology->get('demo_role_direct_supervisor_description'),
            ],
        ];
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function selectRole(string $role): void
    {
        if ($role === 'actual') {
            session()->forget('demo_role_override');
        } else {
            session()->put('demo_role_override', $role);
        }

        // Explicitly save session before redirect
        session()->save();

        $this->currentRole = $role;
        $this->isOpen = false;

        // Use Livewire redirect for proper page reload (no return - matches OrganizationSwitcher pattern)
        $this->redirect(request()->url());
    }

    public function clearDemoRole(): void
    {
        session()->forget('demo_role_override');
        session()->save();

        $this->currentRole = 'actual';

        // Use Livewire redirect for proper page reload (no return - matches OrganizationSwitcher pattern)
        $this->redirect(request()->url());
    }

    public function render()
    {
        // Show for all authenticated users in demo/pilot environment
        // This allows everyone to experience different role perspectives
        $user = auth()->user();
        $canUseDemoSwitcher = $user !== null;

        return view('livewire.demo-role-switcher', [
            'canUseDemoSwitcher' => $canUseDemoSwitcher,
            'isInDemoMode' => $this->currentRole !== 'actual',
        ]);
    }
}
