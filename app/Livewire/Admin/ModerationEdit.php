<?php

namespace App\Livewire\Admin;

use App\Models\ContentModerationResult;
use App\Models\CourseApprovalWorkflow;
use App\Models\MiniCourse;
use App\Services\Moderation\ModerationAssignmentService;
use Livewire\Component;

class ModerationEdit extends Component
{
    public ContentModerationResult $result;

    // Form fields
    public string $title = '';
    public string $description = '';
    public string $rationale = '';
    public string $expectedExperience = '';
    public string $reviewNotes = '';

    // Content type
    public string $contentType = '';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rationale' => 'nullable|string',
            'expectedExperience' => 'nullable|string',
        ];
    }

    public function mount(ContentModerationResult $result): void
    {
        $this->result = $result->load('moderatable');

        if (!$this->result->moderatable) {
            session()->flash('error', 'Content not found.');
            $this->redirect(route('admin.moderation'));
            return;
        }

        $moderatable = $this->result->moderatable;
        $this->contentType = class_basename(get_class($moderatable));

        // Load form fields based on content type
        $this->title = $moderatable->title ?? '';
        $this->description = $moderatable->description ?? '';

        if ($this->contentType === 'MiniCourse') {
            $this->rationale = $moderatable->rationale ?? '';
            $this->expectedExperience = $moderatable->expected_experience ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $moderatable = $this->result->moderatable;

        $updateData = [
            'title' => $this->title,
            'description' => $this->description,
        ];

        if ($this->contentType === 'MiniCourse') {
            $updateData['rationale'] = $this->rationale;
            $updateData['expected_experience'] = $this->expectedExperience;
        }

        $moderatable->update($updateData);

        // Trigger re-moderation if the trait method exists
        if (method_exists($moderatable, 'queueModeration')) {
            $moderatable->queueModeration();
        }

        session()->flash('success', 'Content updated. Re-moderation has been triggered.');
        $this->redirect(route('admin.moderation'));
    }

    public function saveAndApprove(): void
    {
        $this->validate();

        $moderatable = $this->result->moderatable;

        $updateData = [
            'title' => $this->title,
            'description' => $this->description,
        ];

        if ($this->contentType === 'MiniCourse') {
            $updateData['rationale'] = $this->rationale;
            $updateData['expected_experience'] = $this->expectedExperience;
        }

        $moderatable->update($updateData);

        // Approve without re-moderation
        $this->result->approve(auth()->id(), $this->reviewNotes ?: 'Approved after content edits');

        app(ModerationAssignmentService::class)->notifyModerationComplete($this->result, 'approved');

        session()->flash('success', 'Content updated and approved.');
        $this->redirect(route('admin.moderation'));
    }

    public function saveAndPublish(): void
    {
        if ($this->contentType !== 'MiniCourse') {
            session()->flash('error', 'Only Mini Courses can be published.');
            return;
        }

        $this->validate();

        $moderatable = $this->result->moderatable;

        // Update content
        $moderatable->update([
            'title' => $this->title,
            'description' => $this->description,
            'rationale' => $this->rationale,
            'expected_experience' => $this->expectedExperience,
        ]);

        // Approve the moderation result
        $this->result->approve(auth()->id(), $this->reviewNotes ?: 'Approved and published after content edits');

        // Create/update approval workflow as approved
        $workflow = CourseApprovalWorkflow::firstOrNew([
            'mini_course_id' => $moderatable->id,
        ]);

        $workflow->fill([
            'status' => CourseApprovalWorkflow::STATUS_APPROVED,
            'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'submitted_by' => $moderatable->created_by ?? auth()->id(),
            'submitted_at' => $workflow->submitted_at ?? now(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes ?: 'Approved and published from moderation edit',
        ]);

        $workflow->save();

        // Update course to approved and active
        $moderatable->update([
            'approval_status' => MiniCourse::APPROVAL_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $this->reviewNotes ?: 'Approved and published from moderation edit',
            'status' => MiniCourse::STATUS_ACTIVE,
        ]);

        app(ModerationAssignmentService::class)->notifyModerationComplete($this->result, 'approved');

        session()->flash('success', 'Course updated, approved, and published successfully.');
        $this->redirect(route('admin.moderation'));
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.moderation'));
    }

    public function render()
    {
        return view('livewire.admin.moderation-edit', [
            'moderatable' => $this->result->moderatable,
        ])->layout('components.layouts.dashboard', ['title' => 'Edit Content - Moderation']);
    }
}
