<?php

namespace App\Livewire\Dashboard;

use App\Models\Dashboard;
use App\Models\DashboardWidget;
use Illuminate\Support\Collection;
use Livewire\Component;

class DashboardIndex extends Component
{
    public ?int $selectedDashboardId = null;

    public ?Dashboard $currentDashboard = null;

    // Create dashboard modal
    public bool $showCreateModal = false;

    public string $newDashboardName = '';

    public string $newDashboardDescription = '';

    public bool $createFromTemplate = false;

    // Add widget panel
    public bool $showWidgetPanel = false;

    public string $newWidgetType = '';

    public string $newWidgetTitle = '';

    public array $newWidgetConfig = [];

    // Edit widget modal
    public bool $showEditWidgetModal = false;

    public ?int $editingWidgetId = null;

    public string $editWidgetTitle = '';

    public array $editWidgetConfig = [];

    // Actions menu
    public bool $showActionsMenu = false;

    public bool $showShareMenu = false;

    // Date range
    public string $dateRange = 'week';

    public ?string $startDate = null;

    public ?string $endDate = null;

    protected $listeners = ['widgetMoved' => 'updateWidgetPosition'];

    public function mount(): void
    {
        $user = auth()->user();

        // Try to get last viewed dashboard or default
        $this->currentDashboard = Dashboard::getDefaultForUser($user);

        if (! $this->currentDashboard) {
            // Create a default dashboard if none exists
            $this->currentDashboard = Dashboard::createDefault($user);
        }

        $this->selectedDashboardId = $this->currentDashboard->id;
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->endOfWeek()->format('Y-m-d');
    }

    public function getDashboardsProperty(): Collection
    {
        return Dashboard::accessibleBy(auth()->user())
            ->orderBy('name')
            ->get();
    }

    public function getMyDashboardsProperty(): Collection
    {
        return Dashboard::ownedBy(auth()->user())
            ->orderBy('name')
            ->get();
    }

    public function getSharedDashboardsProperty(): Collection
    {
        return Dashboard::sharedInOrg(auth()->user()->org_id)
            ->where('user_id', '!=', auth()->id())
            ->orderBy('name')
            ->get();
    }

    public function getWidgetTypesProperty(): array
    {
        return Dashboard::getWidgetTypes();
    }

    public function selectDashboard(int $id): void
    {
        $dashboard = Dashboard::accessibleBy(auth()->user())->find($id);

        if ($dashboard) {
            $this->currentDashboard = $dashboard;
            $this->selectedDashboardId = $id;
        }
    }

    public function openCreateModal(): void
    {
        $this->reset(['newDashboardName', 'newDashboardDescription', 'createFromTemplate']);
        $this->showCreateModal = true;
    }

    public function createDashboard(): void
    {
        $this->validate([
            'newDashboardName' => 'required|min:2|max:255',
        ]);

        $user = auth()->user();

        if ($this->createFromTemplate) {
            $dashboard = Dashboard::createDefault($user, $this->newDashboardName);
        } else {
            $dashboard = Dashboard::create([
                'org_id' => $user->org_id,
                'user_id' => $user->id,
                'name' => $this->newDashboardName,
                'description' => $this->newDashboardDescription,
            ]);
        }

        $this->currentDashboard = $dashboard;
        $this->selectedDashboardId = $dashboard->id;
        $this->showCreateModal = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard created successfully!',
        ]);
    }

    public function deleteDashboard(): void
    {
        if (! $this->currentDashboard || $this->currentDashboard->user_id !== auth()->id()) {
            return;
        }

        $this->currentDashboard->delete();

        // Switch to another dashboard
        $this->currentDashboard = Dashboard::getDefaultForUser(auth()->user());

        if (! $this->currentDashboard) {
            $this->currentDashboard = Dashboard::createDefault(auth()->user());
        }

        $this->selectedDashboardId = $this->currentDashboard->id;
        $this->showActionsMenu = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard deleted.',
        ]);
    }

    public function toggleShare(): void
    {
        if (! $this->currentDashboard || $this->currentDashboard->user_id !== auth()->id()) {
            return;
        }

        $this->currentDashboard->update([
            'is_shared' => ! $this->currentDashboard->is_shared,
        ]);

        $this->currentDashboard->refresh();
        $this->showShareMenu = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->currentDashboard->is_shared
                ? 'Dashboard is now shared with your organization.'
                : 'Dashboard is now private.',
        ]);
    }

    public function setAsDefault(): void
    {
        if (! $this->currentDashboard) {
            return;
        }

        $this->currentDashboard->setAsDefault();
        $this->showActionsMenu = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard set as default.',
        ]);
    }

    public function openWidgetPanel(): void
    {
        $this->reset(['newWidgetType', 'newWidgetTitle', 'newWidgetConfig']);
        $this->showWidgetPanel = true;
    }

    public function selectWidgetType(string $type): void
    {
        $this->newWidgetType = $type;
        $this->newWidgetTitle = Dashboard::getWidgetTypes()[$type]['label'] ?? 'New Widget';
        $this->newWidgetConfig = $this->getDefaultWidgetConfig($type);
    }

    protected function getDefaultWidgetConfig(string $type): array
    {
        return match ($type) {
            Dashboard::WIDGET_METRIC_CARD => [
                'data_source' => 'learners_total',
                'color' => 'blue',
            ],
            Dashboard::WIDGET_BAR_CHART => [
                'data_source' => 'survey_responses_weekly',
                'compare' => true,
            ],
            Dashboard::WIDGET_LINE_CHART => [
                'data_source' => 'survey_responses_trend',
                'days' => 30,
            ],
            Dashboard::WIDGET_STUDENT_LIST => [
                'filter' => 'high_risk',
                'limit' => 5,
            ],
            Dashboard::WIDGET_SURVEY_SUMMARY => [
                'limit' => 5,
            ],
            Dashboard::WIDGET_ALERT_FEED => [
                'limit' => 10,
            ],
            Dashboard::WIDGET_NOTIFICATION_FEED => [
                'limit' => 10,
            ],
            default => [],
        };
    }

    public function addWidget(): void
    {
        if (! $this->currentDashboard || ! $this->newWidgetType) {
            return;
        }

        $this->currentDashboard->addWidget(
            $this->newWidgetType,
            $this->newWidgetTitle,
            $this->newWidgetConfig
        );

        $this->currentDashboard->refresh();
        $this->showWidgetPanel = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Widget added!',
        ]);
    }

    public function editWidget(int $widgetId): void
    {
        $widget = DashboardWidget::find($widgetId);

        if (! $widget || $widget->dashboard_id !== $this->currentDashboard?->id) {
            return;
        }

        $this->editingWidgetId = $widgetId;
        $this->editWidgetTitle = $widget->title;
        $this->editWidgetConfig = $widget->config ?? [];
        $this->showEditWidgetModal = true;
    }

    public function updateWidget(): void
    {
        $widget = DashboardWidget::find($this->editingWidgetId);

        if (! $widget || $widget->dashboard_id !== $this->currentDashboard?->id) {
            return;
        }

        $widget->update([
            'title' => $this->editWidgetTitle,
            'config' => $this->editWidgetConfig,
        ]);

        $this->currentDashboard->refresh();
        $this->showEditWidgetModal = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Widget updated!',
        ]);
    }

    public function deleteWidget(int $widgetId): void
    {
        $widget = DashboardWidget::find($widgetId);

        if (! $widget || $widget->dashboard_id !== $this->currentDashboard?->id) {
            return;
        }

        $widget->delete();
        $this->currentDashboard->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Widget removed.',
        ]);
    }

    public function updateWidgetPosition(int $widgetId, array $position): void
    {
        $widget = DashboardWidget::find($widgetId);

        if (! $widget || $widget->dashboard_id !== $this->currentDashboard?->id) {
            return;
        }

        $widget->update(['position' => $position]);
    }

    public function reorderWidgets(array $order): void
    {
        foreach ($order as $index => $widgetId) {
            DashboardWidget::where('id', $widgetId)
                ->where('dashboard_id', $this->currentDashboard?->id)
                ->update(['order' => $index]);
        }

        $this->currentDashboard->refresh();
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;

        $this->startDate = match ($range) {
            'today' => now()->format('Y-m-d'),
            'week' => now()->startOfWeek()->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'quarter' => now()->startOfQuarter()->format('Y-m-d'),
            default => now()->startOfWeek()->format('Y-m-d'),
        };

        $this->endDate = match ($range) {
            'today' => now()->format('Y-m-d'),
            'week' => now()->endOfWeek()->format('Y-m-d'),
            'month' => now()->endOfMonth()->format('Y-m-d'),
            'quarter' => now()->endOfQuarter()->format('Y-m-d'),
            default => now()->endOfWeek()->format('Y-m-d'),
        };
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-index', [
            'widgets' => $this->currentDashboard?->widgets ?? collect(),
            'orgId' => auth()->user()->org_id,
        ])->layout('components.layouts.dashboard', ['title' => 'Dashboard']);
    }
}
