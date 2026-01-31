<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Survey;
use App\Models\CustomReport;
use App\Models\Resource;
use Livewire\Component;

class PushContentModal extends Component
{
    public bool $show = false;
    public string $contentType = '';
    public int $contentId = 0;
    public array $selectedOrgIds = [];
    public bool $selectAll = false;

    protected $listeners = [
        'openPushSurvey' => 'openSurvey',
        'openPushReport' => 'openReport',
        'openPushResource' => 'openResource',
    ];

    public function openSurvey(int $id): void
    {
        $this->contentType = 'survey';
        $this->contentId = $id;
        $this->reset(['selectedOrgIds', 'selectAll']);
        $this->show = true;
    }

    public function openReport(int $id): void
    {
        $this->contentType = 'report';
        $this->contentId = $id;
        $this->reset(['selectedOrgIds', 'selectAll']);
        $this->show = true;
    }

    public function openResource(int $id): void
    {
        $this->contentType = 'resource';
        $this->contentId = $id;
        $this->reset(['selectedOrgIds', 'selectAll']);
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->reset(['selectedOrgIds', 'selectAll', 'contentType', 'contentId']);
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedOrgIds = $this->downstreamOrgs->pluck('id')->toArray();
        } else {
            $this->selectedOrgIds = [];
        }
    }

    public function push(): void
    {
        if (empty($this->selectedOrgIds)) {
            return;
        }

        $content = $this->getContent();
        if (!$content) {
            session()->flash('error', 'Content not found.');
            return;
        }

        $userOrg = auth()->user()->organization;
        $pushed = [];
        $errors = [];

        foreach ($this->selectedOrgIds as $orgId) {
            $targetOrg = Organization::find($orgId);

            if (!$targetOrg || !$userOrg->canPushContentTo($targetOrg)) {
                $errors[] = "Cannot push to {$targetOrg->org_name}";
                continue;
            }

            $content->pushToOrganization($targetOrg, auth()->id());
            $pushed[] = $targetOrg->org_name;
        }

        $this->close();

        if (count($pushed) > 0) {
            $typeLabel = match($this->contentType) {
                'survey' => 'Survey',
                'report' => 'Report',
                'resource' => 'Resource',
                default => 'Content',
            };
            session()->flash('success', "{$typeLabel} pushed to: " . implode(', ', $pushed));
        }

        if (count($errors) > 0) {
            session()->flash('error', implode('. ', $errors));
        }
    }

    protected function getContent()
    {
        return match($this->contentType) {
            'survey' => Survey::find($this->contentId),
            'report' => CustomReport::find($this->contentId),
            'resource' => Resource::find($this->contentId),
            default => null,
        };
    }

    public function getDownstreamOrgsProperty()
    {
        $userOrg = auth()->user()->organization;
        return $userOrg ? $userOrg->getDownstreamOrganizations() : collect();
    }

    public function getContentTitleProperty(): string
    {
        $content = $this->getContent();
        if (!$content) return '';

        return match($this->contentType) {
            'survey' => $content->title ?? 'Survey',
            'report' => $content->report_name ?? 'Report',
            'resource' => $content->title ?? 'Resource',
            default => 'Content',
        };
    }

    public function getContentTypeLabelProperty(): string
    {
        return match($this->contentType) {
            'survey' => 'Survey',
            'report' => 'Report',
            'resource' => 'Resource',
            default => 'Content',
        };
    }

    public function render()
    {
        return view('livewire.push-content-modal', [
            'downstreamOrgs' => $this->downstreamOrgs,
            'contentTitle' => $this->contentTitle,
            'contentTypeLabel' => $this->contentTypeLabel,
        ]);
    }
}
