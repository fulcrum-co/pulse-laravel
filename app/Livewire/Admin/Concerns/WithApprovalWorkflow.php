<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\ContentModerationResult;
use App\Models\CourseApprovalWorkflow;
use App\Models\MiniCourse;
use App\Services\Moderation\ModerationAssignmentService;

trait WithApprovalWorkflow
{
    public function submitForApproval(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        if (! ($moderatable instanceof MiniCourse)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Approval workflow is only available for Mini Courses.',
            ]);

            return;
        }

        // Check if moderation has passed
        if (! $result->status === ContentModerationResult::STATUS_PASSED &&
            ! $result->status === ContentModerationResult::STATUS_APPROVED_OVERRIDE) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Content must pass moderation before submitting for approval.',
            ]);

            return;
        }

        $workflow = CourseApprovalWorkflow::firstOrNew([
            'mini_course_id' => $moderatable->id,
        ]);

        $workflow->fill([
            'status' => CourseApprovalWorkflow::STATUS_PENDING,
            'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        $workflow->save();

        $moderatable->update([
            'approval_status' => MiniCourse::APPROVAL_PENDING,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content submitted for approval.',
        ]);

        $this->closeReviewModal();
    }

    public function approveAndPublish(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $result->approve(auth()->id(), $this->reviewNotes);

        $moderatable = $result->moderatable;

        if ($moderatable instanceof MiniCourse) {
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
                'review_notes' => $this->reviewNotes,
            ]);

            $workflow->save();

            $moderatable->update([
                'approval_status' => MiniCourse::APPROVAL_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $this->reviewNotes,
                'status' => MiniCourse::STATUS_ACTIVE,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Content approved and published.',
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Content approved.',
            ]);
        }

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->closeReviewModal();
    }
}
