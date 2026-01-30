<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Component;

class OrganizationSwitcher extends Component
{
    public bool $showSwitcher = false;

    protected $listeners = ['organizationSwitched' => '$refresh'];

    public function toggleSwitcher(): void
    {
        $this->showSwitcher = !$this->showSwitcher;
    }

    public function switchOrganization(int $orgId): void
    {
        $user = auth()->user();

        if ($user->switchOrganization($orgId)) {
            $this->showSwitcher = false;

            // Refresh the page to reflect new organization context
            $this->dispatch('organizationSwitched');
            $this->redirect(request()->header('Referer') ?? '/dashboard');
        }
    }

    public function resetToHome(): void
    {
        $user = auth()->user();
        $user->resetToHomeOrganization();

        $this->showSwitcher = false;
        $this->dispatch('organizationSwitched');
        $this->redirect(request()->header('Referer') ?? '/dashboard');
    }

    public function getCurrentOrganizationProperty(): ?Organization
    {
        return auth()->user()->getEffectiveOrganization();
    }

    public function getAccessibleOrganizationsProperty()
    {
        return auth()->user()->getAccessibleOrganizations();
    }

    public function getIsViewingChildOrgProperty(): bool
    {
        $user = auth()->user();
        return $user->current_org_id !== null && $user->current_org_id !== $user->org_id;
    }

    public function render()
    {
        return view('livewire.organization-switcher', [
            'currentOrg' => $this->currentOrganization,
            'accessibleOrgs' => $this->accessibleOrganizations,
            'isViewingChildOrg' => $this->isViewingChildOrg,
        ]);
    }
}
