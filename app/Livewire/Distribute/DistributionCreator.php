<?php

namespace App\Livewire\Distribute;

use App\Models\ContactList;
use App\Models\CustomReport;
use App\Models\Distribution;
use App\Models\MessageTemplate;
use App\Models\Student;
use Livewire\Component;

class DistributionCreator extends Component
{
    // Distribution ID for editing
    public ?int $distributionId = null;

    // Basics
    public string $title = '';

    public string $description = '';

    public string $channel = 'email';

    public string $distributionType = 'one_time';

    // Recipients (To field) - supports multiple contact lists and/or individual contacts
    public array $selectedContactListIds = [];

    public array $selectedContactIds = [];

    // Content
    public string $subject = '';

    public bool $linkReports = false;

    public array $selectedReportIds = [];

    public string $reportMode = 'live';

    public string $messageBody = '';

    public ?int $messageTemplateId = null;

    // Schedule
    public bool $sendImmediately = true;

    public ?string $scheduledFor = null;

    public string $scheduleType = 'interval';

    public string $intervalType = 'weekly';

    public int $intervalValue = 1;

    public array $customDays = [];

    public string $sendTime = '09:00';

    public string $timezone = 'America/New_York';

    // Search for contacts
    public string $contactSearch = '';

    // Search for reports
    public string $reportSearch = '';

    public function mount(?int $distribution = null): void
    {
        if ($distribution) {
            $this->distributionId = $distribution;
            $this->loadDistribution();
        }
    }

    protected function loadDistribution(): void
    {
        $distribution = Distribution::where('org_id', auth()->user()->org_id)
            ->find($this->distributionId);

        if (! $distribution) {
            return;
        }

        $this->title = $distribution->title;
        $this->description = $distribution->description ?? '';
        $this->channel = $distribution->channel;
        $this->distributionType = $distribution->distribution_type;

        // Load multiple contact lists
        $this->selectedContactListIds = $distribution->contact_list_ids ?? [];
        // Fallback to single contact_list_id for backward compatibility
        if (empty($this->selectedContactListIds) && $distribution->contact_list_id) {
            $this->selectedContactListIds = [$distribution->contact_list_id];
        }

        // Load individual contacts
        $this->selectedContactIds = $distribution->recipient_ids ?? [];

        // Load multiple reports
        $this->selectedReportIds = $distribution->report_ids ?? [];
        // Fallback to single report_id for backward compatibility
        if (empty($this->selectedReportIds) && $distribution->report_id) {
            $this->selectedReportIds = [$distribution->report_id];
        }
        $this->linkReports = ! empty($this->selectedReportIds);

        $this->reportMode = $distribution->report_mode ?? 'live';
        $this->subject = $distribution->subject ?? '';
        $this->messageBody = $distribution->message_body ?? '';
        $this->messageTemplateId = $distribution->message_template_id;
        $this->scheduledFor = $distribution->scheduled_for?->format('Y-m-d\TH:i');
        $this->sendImmediately = ! $distribution->scheduled_for && $distribution->distribution_type === 'one_time';
        $this->timezone = $distribution->timezone ?? 'America/New_York';

        if ($distribution->schedule) {
            $this->scheduleType = $distribution->schedule->schedule_type;
            $this->intervalType = $distribution->schedule->interval_type ?? 'weekly';
            $this->intervalValue = $distribution->schedule->interval_value ?? 1;
            $this->customDays = $distribution->schedule->custom_days ?? [];
            $this->sendTime = $distribution->schedule->send_time ?? '09:00';
        }
    }

    public function toggleContactList(int $listId): void
    {
        if (in_array($listId, $this->selectedContactListIds)) {
            $this->selectedContactListIds = array_values(array_diff($this->selectedContactListIds, [$listId]));
        } else {
            $this->selectedContactListIds[] = $listId;
        }
    }

    public function toggleReport(int $reportId): void
    {
        if (in_array($reportId, $this->selectedReportIds)) {
            $this->selectedReportIds = array_values(array_diff($this->selectedReportIds, [$reportId]));
        } else {
            $this->selectedReportIds[] = $reportId;
            // Clear search after adding a report
            $this->reportSearch = '';
        }
    }

    public function toggleContact(int $contactId): void
    {
        if (in_array($contactId, $this->selectedContactIds)) {
            $this->selectedContactIds = array_values(array_diff($this->selectedContactIds, [$contactId]));
        } else {
            $this->selectedContactIds[] = $contactId;
            // Clear search after adding a contact
            $this->contactSearch = '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $data = [
            'org_id' => auth()->user()->org_id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'distribution_type' => $this->distributionType,
            'channel' => $this->channel,
            'status' => Distribution::STATUS_DRAFT,
            'content_type' => ! empty($this->selectedReportIds) ? 'report' : 'custom',
            'report_ids' => ! empty($this->selectedReportIds) ? $this->selectedReportIds : null,
            'report_id' => ! empty($this->selectedReportIds) ? $this->selectedReportIds[0] : null, // Keep for backward compat
            'report_mode' => ! empty($this->selectedReportIds) ? $this->reportMode : null,
            'subject' => $this->channel === 'email' ? $this->subject : null,
            'message_body' => $this->messageBody ?: null,
            'message_template_id' => $this->messageTemplateId,
            'recipient_type' => ! empty($this->selectedContactListIds) ? 'contact_list' : 'individual',
            'contact_list_ids' => ! empty($this->selectedContactListIds) ? $this->selectedContactListIds : null,
            'contact_list_id' => ! empty($this->selectedContactListIds) ? $this->selectedContactListIds[0] : null, // Backward compat
            'recipient_ids' => ! empty($this->selectedContactIds) ? $this->selectedContactIds : null,
            'scheduled_for' => ! $this->sendImmediately && $this->scheduledFor ? $this->scheduledFor : null,
            'timezone' => $this->timezone,
            'created_by' => auth()->id(),
        ];

        if ($this->distributionId) {
            $distribution = Distribution::where('org_id', auth()->user()->org_id)
                ->find($this->distributionId);
            $distribution->update($data);
        } else {
            $distribution = Distribution::create($data);
        }

        // Handle recurring schedule
        if ($this->distributionType === 'recurring') {
            $scheduleData = [
                'schedule_type' => $this->scheduleType,
                'interval_type' => $this->scheduleType === 'interval' ? $this->intervalType : null,
                'interval_value' => $this->scheduleType === 'interval' ? $this->intervalValue : 1,
                'custom_days' => $this->scheduleType === 'custom' ? $this->customDays : null,
                'send_time' => $this->sendTime,
                'timezone' => $this->timezone,
                'is_active' => true,
            ];

            if ($distribution->schedule) {
                $distribution->schedule->update($scheduleData);
            } else {
                $distribution->schedule()->create($scheduleData);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->distributionId ? 'Distribution updated successfully.' : 'Distribution created successfully.',
        ]);

        $this->redirect(route('distribute.show', $distribution));
    }

    public function getSelectedRecipientsCountProperty(): int
    {
        $count = 0;

        // Count members from selected contact lists
        foreach ($this->selectedContactListIds as $listId) {
            $list = ContactList::find($listId);
            if ($list) {
                $count += $list->member_count;
            }
        }

        // Add individual contacts (excluding duplicates would need more complex logic)
        $count += count($this->selectedContactIds);

        return $count;
    }

    public function render()
    {
        $contactLists = ContactList::where('org_id', auth()->user()->org_id)->get();

        // Add member count using the model accessor
        $contactLists->each(function ($list) {
            $list->members_count = $list->member_count;
        });

        // Get individual contacts for search
        $contacts = collect();
        if ($this->contactSearch) {
            $contacts = Student::where('org_id', auth()->user()->org_id)
                ->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', "%{$this->contactSearch}%")
                        ->orWhere('last_name', 'like', "%{$this->contactSearch}%")
                        ->orWhere('email', 'like', "%{$this->contactSearch}%");
                })
                ->with('user')
                ->limit(10)
                ->get();
        }

        // Get selected contacts for displaying in tags
        $selectedContacts = collect();
        if (! empty($this->selectedContactIds)) {
            $selectedContacts = Student::whereIn('id', $this->selectedContactIds)
                ->with('user')
                ->get()
                ->keyBy('id');
        }

        // Get reports with optional search filter
        $reportsQuery = CustomReport::where('org_id', auth()->user()->org_id);
        if ($this->reportSearch) {
            $reportsQuery->where('report_name', 'like', "%{$this->reportSearch}%");
        }
        $reports = $reportsQuery->orderBy('report_name')->get();

        // Get selected reports for displaying (even if filtered out by search)
        $selectedReports = collect();
        if (! empty($this->selectedReportIds)) {
            $selectedReports = CustomReport::whereIn('id', $this->selectedReportIds)
                ->get()
                ->keyBy('id');
        }

        return view('livewire.distribute.distribution-creator', [
            'contactLists' => $contactLists,
            'reports' => $reports,
            'selectedReports' => $selectedReports,
            'contacts' => $contacts,
            'selectedContacts' => $selectedContacts,
            'templates' => MessageTemplate::where('org_id', auth()->user()->org_id)
                ->where('channel', $this->channel)
                ->get(),
        ])->layout('components.layouts.dashboard', [
            'title' => $this->distributionId ? 'Edit Distribution' : 'Create Distribution',
        ]);
    }
}
