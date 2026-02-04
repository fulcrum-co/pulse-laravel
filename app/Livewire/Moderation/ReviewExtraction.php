<?php

namespace App\Livewire\Moderation;

use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\PlanLink;
use App\Models\PendingExtraction;
use App\Services\NotificationService;
use App\Services\ResourceRecommendationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ReviewExtraction extends Component
{
    use AuthorizesRequests;
    public PendingExtraction $extraction;

    public array $editableData = [];

    public function mount(int $id): void
    {
        $this->extraction = PendingExtraction::with(['contact', 'collectionEvent.organization.settings'])->findOrFail($id);
        $this->authorize('view', $this->extraction);
        $this->editableData = $this->extraction->extracted_data ?? [];
    }

    public function apply(NotificationService $notificationService): void
    {
        DB::transaction(function () {
            $contact = $this->extraction->contact;
            $oldValues = $contact->getOriginal();

            $contact->update($this->editableData);

            $this->extraction->update(['status' => 'applied']);

            if ($this->extraction->collectionEvent->is_anonymous) {
                $this->extraction->update(['raw_transcript' => null]);
            }

            AuditLog::log(AuditLog::ACTION_UPDATE, $this->extraction, $oldValues, $this->editableData, $contact);
        });

        $admins = \App\Models\User::where('org_id', $this->extraction->contact->org_id)
            ->whereIn('primary_role', ['admin', 'consultant'])
            ->pluck('id')
            ->all();

        $notificationService->notifyMany($admins, 'collection', 'extraction_applied', [
            'title' => 'Extraction applied',
            'body' => 'A narrative update was applied to a contact record.',
            'notifiable_type' => PendingExtraction::class,
            'notifiable_id' => $this->extraction->id,
        ]);

        session()->flash('message', 'Record updated successfully.');
        $this->redirectRoute('admin.moderation');
    }

    public function reject(): void
    {
        $this->extraction->update(['status' => 'rejected']);
        $this->redirectRoute('admin.moderation');
    }

    public function addResourceToPlan(int $resourceId): void
    {
        $contact = $this->extraction->contact;
        $plan = Plan::firstOrCreate(
            [
                'plannable_type' => get_class($contact),
                'plannable_id' => $contact->id,
                'status' => 'active',
            ],
            [
                'title' => 'Active Plan',
            ]
        );

        PlanLink::firstOrCreate([
            'plan_id' => $plan->id,
            'linkable_type' => \App\Models\Resource::class,
            'linkable_id' => $resourceId,
        ]);

        session()->flash('message', 'Resource added to plan.');
    }

    public function render(ResourceRecommendationService $recommendationService)
    {
        $org = $this->extraction->collectionEvent->organization;
        $settings = $org?->settings ?? $org?->getOrCreateSettings();
        $suggestions = $recommendationService->getSuggestions($this->extraction);

        return view('livewire.moderation.review-extraction', [
            'org' => $org,
            'settings' => $settings,
            'suggestions' => $suggestions,
        ]);
    }
}
