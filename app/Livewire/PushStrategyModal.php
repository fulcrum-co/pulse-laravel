<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use App\Models\Organization;
use Livewire\Component;

class PushStrategyModal extends Component
{
    public $show = false;
    public StrategicPlan $strategy;
    public $selectedOrgId = null;
    public $includeSurveys = true;

    protected $listeners = ['openPushStrategy' => 'open'];

    public function mount(StrategicPlan $strategy)
    {
        $this->strategy = $strategy;
    }

    public function open()
    {
        $this->show = true;
        $this->selectedOrgId = null;
        $this->includeSurveys = true;
    }

    public function close()
    {
        $this->show = false;
        $this->selectedOrgId = null;
    }

    public function push()
    {
        if (!$this->selectedOrgId) {
            return;
        }

        $targetOrg = Organization::find($this->selectedOrgId);
        $userOrg = auth()->user()->organization;

        if (!$targetOrg || !$userOrg->canPushContentTo($targetOrg)) {
            session()->flash('error', 'Cannot push to this organization.');
            return;
        }

        $newStrategy = $this->strategy->pushToOrganization($targetOrg);

        $this->close();
        session()->flash('success', 'Strategy pushed to ' . $targetOrg->org_name . ' successfully.');
    }

    public function getDownstreamOrgsProperty()
    {
        $userOrg = auth()->user()->organization;
        return $userOrg->getDownstreamOrganizations();
    }

    public function render()
    {
        return view('livewire.push-strategy-modal', [
            'downstreamOrgs' => $this->downstreamOrgs,
        ]);
    }
}
