<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Survey;
use App\Models\CustomReport;
use App\Models\Resource;
use App\Models\Provider;
use App\Models\Program;
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
        'openPushProvider' => 'openProvider',
        'openPushProgram' => 'openProgram',
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

    public function openProvider(int $id): void
    {
        $this->contentType = 'provider';
        $this->contentId = $id;
        $this->reset(['selectedOrgIds', 'selectAll']);
        $this->show = true;
    }

    public function openProgram(int $id): void
    {
        $this->contentType = 'program';
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

        $user = auth()->user();
        $userOrg = $user->organization;
        $pushed = [];
        $errors = [];

        // Get consultant's assigned org IDs for permission check
        $assignedOrgIds = $user->primary_role === 'consultant'
            ? $user->organizations()->pluck('organizations.id')->toArray()
            : [];

        foreach ($this->selectedOrgIds as $orgId) {
            $targetOrg = Organization::find($orgId);

            if (!$targetOrg) {
                $errors[] = "Organization not found";
                continue;
            }

            // Allow push if: user's org can push to target OR consultant is assigned to target
            $canPush = ($userOrg && $userOrg->canPushContentTo($targetOrg))
                || in_array($orgId, $assignedOrgIds);

            if (!$canPush) {
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
                'provider' => 'Provider',
                'program' => 'Program',
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
            'provider' => Provider::find($this->contentId),
            'program' => Program::find($this->contentId),
            default => null,
        };
    }

    public function getDownstreamOrgsProperty()
    {
        $user = auth()->user();
        $orgs = collect();

        // For admins: get downstream orgs from primary organization
        if ($user->isAdmin() && $user->organization) {
            $orgs = $orgs->merge($user->organization->getDownstreamOrganizations());
        }

        // For consultants: include their assigned organizations
        if ($user->primary_role === 'consultant') {
            $orgs = $orgs->merge($user->organizations);
        }

        return $orgs->unique('id')->sortBy('org_name')->values();
    }

    public function getContentTitleProperty(): string
    {
        $content = $this->getContent();
        if (!$content) return '';

        return match($this->contentType) {
            'survey' => $content->title ?? 'Survey',
            'report' => $content->report_name ?? 'Report',
            'resource' => $content->title ?? 'Resource',
            'provider' => $content->name ?? 'Provider',
            'program' => $content->name ?? 'Program',
            default => 'Content',
        };
    }

    public function getContentTypeLabelProperty(): string
    {
        return match($this->contentType) {
            'survey' => 'Survey',
            'report' => 'Report',
            'resource' => 'Resource',
            'provider' => 'Provider',
            'program' => 'Program',
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
