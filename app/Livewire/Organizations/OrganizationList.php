<?php

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Services\RolePermissions;
use Livewire\Component;

class OrganizationList extends Component
{
    public string $search = '';

    public function render()
    {
        $user = auth()->user();

        if (! RolePermissions::currentUserCanAccess('sub_organizations')) {
            abort(403);
        }

        $query = Organization::query()
            ->where('parent_org_id', $user->org_id)
            ->active()
            ->withCount('users')
            ->orderBy('org_name');

        if ($this->search) {
            $query->where('org_name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.organizations.organization-list', [
            'organizations' => $query->get(),
        ])->layout('components.layouts.dashboard', ['title' => 'Organizations']);
    }
}
