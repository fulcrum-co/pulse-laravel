<?php

namespace App\Livewire;

use Livewire\Component;

class DemoRoleSwitcher extends Component
{
    public string $currentRole = '';
    public bool $isOpen = false;

    public array $availableRoles = [
        'actual' => ['label' => 'My Actual Role', 'icon' => 'user-circle', 'description' => 'Use your real account permissions'],
        'consultant' => ['label' => 'District Consultant', 'icon' => 'academic-cap', 'description' => 'District-level oversight, can push content to schools'],
        'superintendent' => ['label' => 'Superintendent', 'icon' => 'building-library', 'description' => 'District administrator with full access'],
        'school_admin' => ['label' => 'School Administrator', 'icon' => 'building-office-2', 'description' => 'Principal or school-level admin'],
        'counselor' => ['label' => 'School Counselor', 'icon' => 'heart', 'description' => 'Student support and intervention access'],
        'teacher' => ['label' => 'Teacher', 'icon' => 'user-group', 'description' => 'Classroom view with student roster'],
        'student' => ['label' => 'Student', 'icon' => 'face-smile', 'description' => 'Student portal experience'],
        'parent' => ['label' => 'Parent/Guardian', 'icon' => 'home', 'description' => 'Parent portal with child info'],
    ];

    public function mount(): void
    {
        $this->currentRole = session('demo_role_override', 'actual');
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
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

        // Use JavaScript to force a hard page reload
        $this->js('window.location.reload()');
    }

    public function clearDemoRole(): void
    {
        session()->forget('demo_role_override');
        session()->save();

        $this->currentRole = 'actual';

        // Use JavaScript to force a hard page reload
        $this->js('window.location.reload()');
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
