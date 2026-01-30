<?php

namespace App\Livewire\Distribute;

use App\Models\Distribution;
use App\Models\DistributionDelivery;
use Livewire\Component;
use Livewire\WithPagination;

class DistributionDetail extends Component
{
    use WithPagination;

    public Distribution $distribution;
    public bool $showSendModal = false;
    public bool $showDeleteModal = false;

    public function mount(Distribution $distribution): void
    {
        // Ensure user can access this distribution
        if ($distribution->org_id !== auth()->user()->org_id) {
            abort(403);
        }

        $this->distribution = $distribution->load(['contactList', 'report', 'schedule', 'creator']);
    }

    public function activate(): void
    {
        if ($this->distribution->isDraft()) {
            $this->distribution->update(['status' => Distribution::STATUS_ACTIVE]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Distribution activated successfully.',
            ]);
        }
    }

    public function pause(): void
    {
        if ($this->distribution->isActive()) {
            $this->distribution->update(['status' => Distribution::STATUS_PAUSED]);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Distribution paused.',
            ]);
        }
    }

    public function resume(): void
    {
        if ($this->distribution->isPaused()) {
            $this->distribution->update(['status' => Distribution::STATUS_ACTIVE]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Distribution resumed.',
            ]);
        }
    }

    public function openSendModal(): void
    {
        $this->showSendModal = true;
    }

    public function closeSendModal(): void
    {
        $this->showSendModal = false;
    }

    public function sendNow(): void
    {
        // Create a new delivery
        $delivery = $this->distribution->deliveries()->create([
            'status' => DistributionDelivery::STATUS_PENDING,
            'total_recipients' => 0, // Will be calculated by the job
        ]);

        // TODO: Dispatch the actual send job
        // ProcessDistributionJob::dispatch($delivery);

        $this->closeSendModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Distribution send initiated.',
        ]);
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function delete(): void
    {
        $this->distribution->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Distribution deleted.',
        ]);

        $this->redirect(route('distribute.index'));
    }

    public function render()
    {
        $deliveries = $this->distribution->deliveries()
            ->with('recipients')
            ->orderByDesc('created_at')
            ->paginate(10);

        $stats = [
            'total_deliveries' => $this->distribution->deliveries()->count(),
            'total_sent' => $this->distribution->deliveries()->sum('sent_count'),
            'total_opened' => $this->distribution->deliveries()->sum('opened_count'),
            'total_clicked' => $this->distribution->deliveries()->sum('clicked_count'),
            'avg_open_rate' => $this->calculateAverageOpenRate(),
        ];

        return view('livewire.distribute.distribution-detail', [
            'deliveries' => $deliveries,
            'stats' => $stats,
        ])->layout('components.layouts.dashboard', ['title' => $this->distribution->title]);
    }

    protected function calculateAverageOpenRate(): float
    {
        $deliveries = $this->distribution->deliveries()
            ->where('sent_count', '>', 0)
            ->get();

        if ($deliveries->isEmpty()) {
            return 0;
        }

        $totalRate = $deliveries->sum(fn ($d) => $d->getOpenRate());
        return round($totalRate / $deliveries->count(), 1);
    }
}
