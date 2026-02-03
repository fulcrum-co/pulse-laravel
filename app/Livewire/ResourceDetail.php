<?php

namespace App\Livewire;

use App\Models\ContactList;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Learner;
use Livewire\Component;

class ResourceDetail extends Component
{
    public Resource $resource;

    // Assign modal state
    public bool $showAssignModal = false;

    public string $assignType = 'learner'; // learner or list

    public ?int $selectedLearnerId = null;

    public ?int $selectedListId = null;

    public string $assignNote = '';

    public function mount(Resource $resource): void
    {
        // Ensure the user has access to this resource's organization
        if (! auth()->user()->canAccessOrganization($resource->org_id)) {
            abort(403);
        }

        $this->resource = $resource;
    }

    public function openAssignModal(): void
    {
        $this->resetAssignForm();
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->resetAssignForm();
    }

    protected function resetAssignForm(): void
    {
        $this->assignType = 'learner';
        $this->selectedLearnerId = null;
        $this->selectedListId = null;
        $this->assignNote = '';
    }

    public function assignResource(): void
    {
        $user = auth()->user();

        if ($this->assignType === 'learner') {
            $this->validate([
                'selectedLearnerId' => 'required|exists:learners,id',
            ]);

            ResourceAssignment::create([
                'resource_id' => $this->resource->id,
                'learner_id' => $this->selectedLearnerId,
                'assigned_by' => $user->id,
                'notes' => $this->assignNote ?: null,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Resource assigned to learner successfully.',
            ]);

        } elseif ($this->assignType === 'list') {
            $this->validate([
                'selectedListId' => 'required|exists:contact_lists,id',
            ]);

            $list = ContactList::find($this->selectedListId);
            $learners = $list->learners;
            $count = 0;

            foreach ($learners as $learner) {
                // Avoid duplicate assignments
                $exists = ResourceAssignment::where('resource_id', $this->resource->id)
                    ->where('learner_id', $learner->id)
                    ->exists();

                if (! $exists) {
                    ResourceAssignment::create([
                        'resource_id' => $this->resource->id,
                        'learner_id' => $learner->id,
                        'assigned_by' => $user->id,
                        'notes' => $this->assignNote ?: null,
                        'assigned_at' => now(),
                        'status' => 'assigned',
                    ]);
                    $count++;
                }
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Resource assigned to {$count} learners from the list.",
            ]);
        }

        $this->closeAssignModal();
    }

    public function getRelatedResourcesProperty()
    {
        // Get resources in the same category or with overlapping tags
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return Resource::whereIn('org_id', $accessibleOrgIds)
            ->active()
            ->where('id', '!=', $this->resource->id)
            ->where(function ($query) {
                if ($this->resource->category) {
                    $query->where('category', $this->resource->category);
                }
                if ($this->resource->tags && count($this->resource->tags) > 0) {
                    foreach ($this->resource->tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                }
            })
            ->limit(4)
            ->get();
    }

    public function getAssignmentCountProperty(): int
    {
        return $this->resource->assignments()->count();
    }

    public function getLearnersProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return Learner::whereIn('org_id', $accessibleOrgIds)
            ->with('user')
            ->get()
            ->sortBy(fn ($learner) => $learner->user?->name ?? '')
            ->values();
    }

    public function getContactListsProperty()
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return ContactList::whereIn('org_id', $accessibleOrgIds)
            ->whereIn('list_type', ['learner', 'mixed'])
            ->orderBy('name')
            ->get();
    }

    public function getTypeIconProperty(): string
    {
        return match ($this->resource->resource_type) {
            'article' => 'document-text',
            'video' => 'play-circle',
            'worksheet' => 'clipboard-document-list',
            'activity' => 'puzzle-piece',
            'link' => 'link',
            'document' => 'document',
            default => 'document',
        };
    }

    public function getTypeColorProperty(): string
    {
        return match ($this->resource->resource_type) {
            'article' => 'blue',
            'video' => 'red',
            'worksheet' => 'green',
            'activity' => 'purple',
            'link' => 'orange',
            'document' => 'gray',
            default => 'gray',
        };
    }

    public function getPreviewTypeProperty(): string
    {
        // Determine what type of preview to show
        if ($this->resource->resource_type === 'video') {
            // Check if it's a YouTube or Vimeo URL
            if ($this->resource->url && (
                str_contains($this->resource->url, 'youtube.com') ||
                str_contains($this->resource->url, 'youtu.be') ||
                str_contains($this->resource->url, 'vimeo.com')
            )) {
                return 'video_embed';
            }
        }

        if ($this->resource->file_path) {
            $extension = pathinfo($this->resource->file_path, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['pdf'])) {
                return 'pdf';
            }
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return 'image';
            }
            if (in_array(strtolower($extension), ['mp4', 'webm', 'mov'])) {
                return 'video_file';
            }
            if (in_array(strtolower($extension), ['mp3', 'wav', 'ogg'])) {
                return 'audio';
            }
        }

        if ($this->resource->url) {
            return 'link';
        }

        return 'none';
    }

    public function getVideoEmbedUrlProperty(): ?string
    {
        if (! $this->resource->url) {
            return null;
        }

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->resource->url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $this->resource->url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    /**
     * Check if the current user can push content to downstream organizations.
     */
    public function getCanPushProperty(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        // Can push if: has downstream orgs from primary org, OR is consultant with assigned orgs
        return $hasDownstream || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Open the push modal for this resource.
     */
    public function openPushModal(): void
    {
        $this->dispatch('openPushResource', $this->resource->id);
    }

    public function render()
    {
        return view('livewire.resource-detail', [
            'relatedResources' => $this->relatedResources,
            'assignmentCount' => $this->assignmentCount,
            'canPush' => $this->canPush,
        ])->layout('components.layouts.dashboard', [
            'title' => $this->resource->title,
        ]);
    }
}
