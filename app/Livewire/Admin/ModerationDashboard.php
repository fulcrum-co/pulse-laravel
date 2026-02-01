<?php

namespace App\Livewire\Admin;

use App\Models\ModerationDecision;
use App\Models\ModerationQueueItem;
use App\Models\ModerationTeamSetting;
use App\Services\Moderation\ModerationQueueService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
#[Title('Moderation Dashboard')]
class ModerationDashboard extends Component
{
    public string $timeRange = '7d'; // 24h, 7d, 30d

    public string $selectedTeamMember = 'all';

    protected ModerationQueueService $queueService;

    public function boot(ModerationQueueService $queueService): void
    {
        $this->queueService = $queueService;
    }

    #[Computed]
    public function queueStats(): array
    {
        return $this->queueService->getQueueStats(auth()->user()->org_id);
    }

    #[Computed]
    public function teamMembers(): Collection
    {
        return ModerationTeamSetting::forOrganization(auth()->user()->org_id)
            ->with('user')
            ->get()
            ->map(fn ($setting) => [
                'id' => $setting->user_id,
                'name' => $setting->user->full_name ?? 'Unknown',
                'current_load' => $setting->current_load,
                'max_load' => $setting->max_concurrent_items,
                'available' => $setting->is_available,
                'specializations' => $setting->content_specializations ?? [],
            ]);
    }

    #[Computed]
    public function teamPerformance(): Collection
    {
        $orgId = auth()->user()->org_id;

        $teamSettings = ModerationTeamSetting::forOrganization($orgId)
            ->with('user')
            ->get();

        return $teamSettings->map(function ($setting) {
            $stats = $this->queueService->getUserStats($setting->user);

            return [
                'user_id' => $setting->user_id,
                'name' => $setting->user->full_name ?? 'Unknown',
                'avatar' => $setting->user->avatar_url ?? null,
                'completed_today' => $stats['completed_today'],
                'completed_week' => $stats['completed_week'],
                'avg_time' => $this->formatSeconds($stats['avg_time_seconds']),
                'avg_time_seconds' => $stats['avg_time_seconds'],
                'current_load' => $setting->current_load,
                'approval_rate' => $this->calculateApprovalRate($stats['decisions_breakdown']),
            ];
        })->sortByDesc('completed_today')->values();
    }

    #[Computed]
    public function recentDecisions(): Collection
    {
        return ModerationDecision::with(['user', 'queueItem.moderationResult.moderatable'])
            ->whereHas('queueItem', function ($q) {
                $q->forOrganization(auth()->user()->org_id);
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($decision) => [
                'id' => $decision->id,
                'user_name' => $decision->user->full_name ?? 'Unknown',
                'decision' => $decision->decision_label,
                'decision_type' => $decision->decision,
                'color' => $decision->decision_color,
                'content_title' => $decision->queueItem->moderationResult?->moderatable?->title ?? 'Unknown',
                'time_spent' => $decision->formatted_time_spent,
                'created_at' => $decision->created_at->diffForHumans(),
            ]);
    }

    #[Computed]
    public function slaWarnings(): Collection
    {
        return $this->queueService->getItemsDueSoon(auth()->user()->org_id, 24)
            ->map(fn ($item) => [
                'id' => $item->id,
                'content_title' => $item->moderationResult?->moderatable?->title ?? 'Unknown',
                'priority' => $item->priority,
                'assigned_to' => $item->assignee?->full_name ?? 'Unassigned',
                'due_at' => $item->due_at->diffForHumans(),
                'sla_status' => $item->sla_status,
            ]);
    }

    #[Computed]
    public function decisionTrends(): array
    {
        $orgId = auth()->user()->org_id;
        $days = match ($this->timeRange) {
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };

        $startDate = now()->subDays($days);

        $decisions = ModerationDecision::selectRaw('DATE(created_at) as date, decision, COUNT(*) as count')
            ->whereHas('queueItem', fn ($q) => $q->forOrganization($orgId))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'decision')
            ->orderBy('date')
            ->get();

        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= now()) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[$dateStr] = [
                'date' => $currentDate->format('M j'),
                'approve' => 0,
                'reject' => 0,
                'request_changes' => 0,
                'escalate' => 0,
            ];
            $currentDate->addDay();
        }

        foreach ($decisions as $decision) {
            if (isset($dates[$decision->date])) {
                $dates[$decision->date][$decision->decision] = $decision->count;
            }
        }

        return array_values($dates);
    }

    #[Computed]
    public function contentTypeBreakdown(): array
    {
        $orgId = auth()->user()->org_id;

        $items = ModerationQueueItem::forOrganization($orgId)
            ->active()
            ->with('moderationResult')
            ->get();

        $breakdown = $items->groupBy(function ($item) {
            $type = class_basename($item->moderationResult?->moderatable_type ?? 'Unknown');

            return $type;
        })->map->count();

        return $breakdown->toArray();
    }

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
    }

    protected function formatSeconds(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds).'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);

        return "{$minutes}m {$remainingSeconds}s";
    }

    protected function calculateApprovalRate(array $breakdown): float
    {
        $total = array_sum($breakdown);

        if ($total === 0) {
            return 0;
        }

        $approved = $breakdown['approved'] ?? 0;

        return round(($approved / $total) * 100, 1);
    }

    public function render()
    {
        return view('livewire.admin.moderation-dashboard');
    }
}
