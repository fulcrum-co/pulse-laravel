<?php

namespace App\Livewire\Distribute;

use App\Models\ContactList;
use App\Models\CustomReport;
use App\Models\Distribution;
use App\Models\MessageTemplate;
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

    // Content
    public string $contentType = 'custom';

    public ?int $reportId = null;

    public string $reportMode = 'live';

    public string $subject = '';

    public string $messageBody = '';

    public ?int $messageTemplateId = null;

    // Recipients
    public string $recipientType = 'contact_list';

    public ?int $contactListId = null;

    public array $recipientIds = [];

    // Schedule
    public bool $sendImmediately = true;

    public ?string $scheduledFor = null;

    public string $scheduleType = 'interval';

    public string $intervalType = 'weekly';

    public int $intervalValue = 1;

    public array $customDays = [];

    public string $sendTime = '09:00';

    public string $timezone = 'America/New_York';

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
        $this->contentType = $distribution->content_type;
        $this->reportId = $distribution->report_id;
        $this->reportMode = $distribution->report_mode ?? 'live';
        $this->subject = $distribution->subject ?? '';
        $this->messageBody = $distribution->message_body ?? '';
        $this->messageTemplateId = $distribution->message_template_id;
        $this->recipientType = $distribution->recipient_type;
        $this->contactListId = $distribution->contact_list_id;
        $this->recipientIds = $distribution->recipient_ids ?? [];
        $this->scheduledFor = $distribution->scheduled_for?->format('Y-m-d\TH:i');
        $this->sendImmediately = ! $distribution->scheduled_for && $distribution->distribution_type === 'one_time';
        $this->timezone = $distribution->timezone;

        if ($distribution->schedule) {
            $this->scheduleType = $distribution->schedule->schedule_type;
            $this->intervalType = $distribution->schedule->interval_type ?? 'weekly';
            $this->intervalValue = $distribution->schedule->interval_value ?? 1;
            $this->customDays = $distribution->schedule->custom_days ?? [];
            $this->sendTime = $distribution->schedule->send_time ?? '09:00';
        }
    }

    protected function validateForm(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'channel' => 'required|in:email,sms',
            'distributionType' => 'required|in:one_time,recurring',
        ];

        // Content validation
        if ($this->contentType === 'report') {
            $rules['reportId'] = 'required|exists:custom_reports,id';
        } else {
            if ($this->channel === 'email') {
                $rules['subject'] = 'required|string|max:255';
            }
            $rules['messageBody'] = 'required|string';
        }

        // Recipients validation
        if ($this->recipientType === 'contact_list') {
            $rules['contactListId'] = 'required|exists:contact_lists,id';
        }

        $this->validate($rules);
    }

    public function save(): void
    {
        // Only validate title for draft saves
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
            'content_type' => $this->contentType,
            'report_id' => $this->contentType === 'report' ? $this->reportId : null,
            'report_mode' => $this->contentType === 'report' ? $this->reportMode : null,
            'subject' => $this->contentType === 'custom' && $this->channel === 'email' ? $this->subject : null,
            'message_body' => $this->contentType === 'custom' ? $this->messageBody : null,
            'message_template_id' => $this->messageTemplateId,
            'recipient_type' => $this->recipientType,
            'contact_list_id' => $this->recipientType === 'contact_list' ? $this->contactListId : null,
            'recipient_ids' => $this->recipientType === 'individual' ? $this->recipientIds : null,
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

    public function render()
    {
        $contactLists = ContactList::where('org_id', auth()->user()->org_id)->get();

        // Add member count using the model accessor
        $contactLists->each(function ($list) {
            $list->members_count = $list->member_count;
        });

        return view('livewire.distribute.distribution-creator', [
            'contactLists' => $contactLists,
            'reports' => CustomReport::where('org_id', auth()->user()->org_id)->get(),
            'templates' => MessageTemplate::where('org_id', auth()->user()->org_id)
                ->where('channel', $this->channel)
                ->get(),
        ])->layout('components.layouts.dashboard', [
            'title' => $this->distributionId ? 'Edit Distribution' : 'Create Distribution',
        ]);
    }
}
