<?php

namespace App\Livewire;

use Livewire\Component;

class DemoRoleSwitcher extends Component
{
    public string $currentRole = '';

    public bool $isOpen = false;

    public array $availableRoles = [
        'actual' => ['label' => 'My Actual Role', 'icon' => 'user-circle', 'description' => 'Use your real account permissions'],
        'consultant' => ['label' => 'District Consultant', 'icon' => 'academic-cap', 'description' => 'District-level oversight, can push content to organizations'],
        'superintendent' => ['label' => 'Superintendent', 'icon' => 'building-library', 'description' => 'District administrator with full access'],
        'organization_admin' => ['label' => 'Organization Administrator', 'icon' => 'building-office-2', 'description' => 'Principal or organization-level admin'],
        'counselor' => ['label' => 'Organization Counselor', 'icon' => 'heart', 'description' => 'Learner support and intervention access'],
        'teacher' => ['label' => 'Teacher', 'icon' => 'user-group', 'description' => 'Classroom view with learner roster'],
        'learner' => ['label' => 'Learner', 'icon' => 'face-smile', 'description' => 'Learner portal experience'],
        'parent' => ['label' => 'Parent/Guardian', 'icon' => 'home', 'description' => 'Parent portal with child info'],
    ];

    public function mount(): void
    {
        $this->currentRole = session('demo_role_override', 'actual');
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
