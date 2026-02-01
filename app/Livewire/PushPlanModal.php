<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\StrategicPlan;
use Livewire\Component;

class PushPlanModal extends Component
{
    public $show = false;

    public StrategicPlan $plan;

    public $selectedOrgId = null;

    public $includeSurveys = true;

    protected $listeners = ['openPushPlan' => 'open'];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;
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
        if (! $this->selectedOrgId) {
            return;
        }

        $targetOrg = Organization::find($this->selectedOrgId);
        $userOrg = auth()->user()->organization;

        if (! $targetOrg || ! $userOrg->canPushContentTo($targetOrg)) {
            session()->flash('error', 'Cannot push to this organization.');

            return;
        }

        $newPlan = $this->plan->pushToOrganization($targetOrg);

        $this->close();
        session()->flash('success', 'Plan pushed to '.$targetOrg->org_name.' successfully.');
    }

    public function getDownstreamOrgsProperty()
    {
        $userOrg = auth()->user()->organization;

        return $userOrg->getDownstreamOrganizations();
    }

    public function render()
    {
        return view('livewire.push-plan-modal', [
            'downstreamOrgs' => $this->downstreamOrgs,
        ]);
    }
}
