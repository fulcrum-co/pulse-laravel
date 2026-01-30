<?php

namespace App\Livewire;

use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
use App\Models\ContactList;
use Livewire\Component;

class ResourceDetail extends Component
{
    public Resource $resource;

    // Assign modal state
    public bool $showAssignModal = false;
    public string $assignType = 'student'; // student or list
    public ?int $selectedStudentId = null;
    public ?int $selectedListId = null;
    public string $assignNote = '';

    public function mount(Resource $resource): void
    {
        // Ensure the resource belongs to the user's organization
        if ($resource->org_id !== auth()->user()->org_id) {
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
        $this->assignType = 'student';
        $this->selectedStudentId = null;
        $this->selectedListId = null;
        $this->assignNote = '';
    }

    public function assignResource(): void
    {
        $user = auth()->user();

        if ($this->assignType === 'student') {
            $this->validate([
                'selectedStudentId' => 'required|exists:students,id',
            ]);

            ResourceAssignment::create([
                'resource_id' => $this->resource->id,
                'student_id' => $this->selectedStudentId,
                'assigned_by' => $user->id,
                'notes' => $this->assignNote ?: null,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Resource assigned to student successfully.',
            ]);

        } elseif ($this->assignType === 'list') {
            $this->validate([
                'selectedListId' => 'required|exists:contact_lists,id',
            ]);

            $list = ContactList::find($this->selectedListId);
            $students = $list->students;
            $count = 0;

            foreach ($students as $student) {
                // Avoid duplicate assignments
                $exists = ResourceAssignment::where('resource_id', $this->resource->id)
                    ->where('student_id', $student->id)
                    ->exists();

                if (!$exists) {
                    ResourceAssignment::create([
                        'resource_id' => $this->resource->id,
                        'student_id' => $student->id,
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
                'message' => "Resource assigned to {$count} students from the list.",
            ]);
        }

        $this->closeAssignModal();
    }

    public function getRelatedResourcesProperty()
    {
        // Get resources in the same category or with overlapping tags
        return Resource::forOrganization(auth()->user()->org_id)
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

    public function getStudentsProperty()
    {
        return Student::where('org_id', auth()->user()->org_id)
            ->with('user')
            ->get()
            ->sortBy(fn ($student) => $student->user?->name ?? '')
            ->values();
    }

    public function getContactListsProperty()
    {
        return ContactList::where('org_id', auth()->user()->org_id)
            ->whereIn('list_type', ['student', 'mixed'])
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
        if (!$this->resource->url) {
            return null;
        }

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->resource->url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $this->resource->url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.resource-detail', [
            'relatedResources' => $this->relatedResources,
            'assignmentCount' => $this->assignmentCount,
        ])->layout('components.layouts.dashboard', [
            'title' => $this->resource->title,
        ]);
    }
}
