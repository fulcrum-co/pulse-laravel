<?php

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Services\RolePermissions;
use Livewire\Component;

class OrganizationShow extends Component
{
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        if (! RolePermissions::currentUserCanAccess('sub_organizations')) {
            abort(403);
        }

        $user = auth()->user();

        // Ensure this org is accessible (child of user's org or the user's org itself)
        $accessibleIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
        if (! in_array($organization->id, $accessibleIds)) {
            abort(403);
        }

        $this->organization = $organization->loadCount(['users', 'children']);
    }

    public function render()
    {
        $users = $this->organization->users()
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'email', 'primary_role']);

        $childOrgs = $this->organization->children()
            ->active()
            ->withCount('users')
            ->orderBy('org_name')
            ->get();

        return view('livewire.organizations.organization-show', [
            'users' => $users,
            'childOrgs' => $childOrgs,
        ])->layout('components.layouts.dashboard', ['title' => $this->organization->org_name]);
    }
}
