<?php

namespace App\Livewire\Dashboard;

use App\Models\Dashboard;
use Illuminate\Support\Collection;
use Livewire\Component;

class DashboardList extends Component
{
    public string $search = '';

    public string $filter = 'all'; // all, mine, shared

    public string $viewMode = 'grid';

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function getDashboardsProperty(): Collection
    {
        $user = auth()->user();

        $query = Dashboard::query();

        if ($this->filter === 'mine') {
            $query->where('user_id', $user->id);
        } elseif ($this->filter === 'shared') {
            $query->where('org_id', $user->org_id)
                ->where('is_shared', true)
                ->where('user_id', '!=', $user->id);
        } else {
            $query->accessibleBy($user);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        return $query->with(['user', 'widgets'])->orderBy('name')->get();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function deleteDashboard(int $id): void
    {
        $dashboard = Dashboard::find($id);

        if ($dashboard && $dashboard->user_id === auth()->id()) {
            $dashboard->delete();
        }
    }

    public function duplicateDashboard(int $id): void
    {
        $dashboard = Dashboard::find($id);

        if ($dashboard) {
            $newDashboard = $dashboard->replicate();
            $newDashboard->name = $dashboard->name.' (Copy)';
            $newDashboard->user_id = auth()->id();
            $newDashboard->is_default = false;
            $newDashboard->save();

            // Copy widgets
            foreach ($dashboard->widgets as $widget) {
                $newWidget = $widget->replicate();
                $newWidget->dashboard_id = $newDashboard->id;
                $newWidget->save();
            }
        }
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-list', [
            'dashboards' => $this->dashboards,
        ])->layout('components.layouts.dashboard', ['title' => 'Dashboards']);
    }
}
