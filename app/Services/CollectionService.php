<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\CollectionQueueItem;
use App\Models\CollectionSchedule;
use App\Models\CollectionSession;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection as SupportCollection;

class CollectionService
{
    /**
     * Create a new collection.
     */
    public function create(array $data, User $user): Collection
    {
        $data['org_id'] = $user->org_id;
        $data['created_by'] = $user->id;
        $data['status'] = $data['status'] ?? Collection::STATUS_DRAFT;

        return Collection::create($data);
    }

    /**
     * Update a collection.
     */
    public function update(Collection $collection, array $data): Collection
    {
        $collection->update($data);

        return $collection->fresh();
    }

    /**
     * Activate a collection.
     */
    public function activate(Collection $collection): void
    {
        $collection->update(['status' => Collection::STATUS_ACTIVE]);

        // Calculate next scheduled run if there's an active schedule
        $schedule = $collection->getActiveSchedule();
        if ($schedule) {
            $nextRun = $schedule->calculateNextRun();
            $schedule->update(['next_scheduled_at' => $nextRun]);
        }
    }

    /**
     * Pause a collection.
     */
    public function pause(Collection $collection): void
    {
        $collection->update(['status' => Collection::STATUS_PAUSED]);
    }

    /**
     * Archive a collection.
     */
    public function archive(Collection $collection): void
    {
        $collection->update([
            'status' => Collection::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);

        // Deactivate all schedules
        $collection->schedules()->update(['is_active' => false]);
    }

    /**
     * Duplicate a collection.
     */
    public function duplicate(Collection $collection, User $user): Collection
    {
        $newCollection = $collection->replicate(['id', 'created_at', 'updated_at']);
        $newCollection->title = $collection->title.' (Copy)';
        $newCollection->status = Collection::STATUS_DRAFT;
        $newCollection->created_by = $user->id;
        $newCollection->archived_at = null;
        $newCollection->save();

        // Duplicate schedules
        foreach ($collection->schedules as $schedule) {
            $newSchedule = $schedule->replicate(['id', 'created_at', 'updated_at']);
            $newSchedule->collection_id = $newCollection->id;
            $newSchedule->is_active = false;
            $newSchedule->last_triggered_at = null;
            $newSchedule->next_scheduled_at = null;
            $newSchedule->save();
        }

        return $newCollection;
    }

    /**
     * Create a schedule for a collection.
     */
    public function createSchedule(Collection $collection, array $config): CollectionSchedule
    {
        $schedule = CollectionSchedule::create([
            'collection_id' => $collection->id,
            'schedule_type' => $config['schedule_type'] ?? CollectionSchedule::TYPE_INTERVAL,
            'interval_type' => $config['interval_type'] ?? null,
            'interval_value' => $config['interval_value'] ?? 1,
            'custom_days' => $config['custom_days'] ?? null,
            'custom_times' => $config['custom_times'] ?? ['09:00'],
            'event_trigger' => $config['event_trigger'] ?? null,
            'timezone' => $config['timezone'] ?? 'America/New_York',
            'start_date' => $config['start_date'] ?? now(),
            'end_date' => $config['end_date'] ?? null,
            'is_active' => $config['is_active'] ?? true,
        ]);

        // Calculate next run
        $schedule->update([
            'next_scheduled_at' => $schedule->calculateNextRun(),
        ]);

        return $schedule;
    }

    /**
     * Create a new session for a collection.
     */
    public function createSession(
        Collection $collection,
        ?CollectionSchedule $schedule = null,
        ?Carbon $date = null
    ): CollectionSession {
        return CollectionSession::create([
            'collection_id' => $collection->id,
            'schedule_id' => $schedule?->id,
            'session_date' => $date ?? now()->toDateString(),
            'status' => CollectionSession::STATUS_PENDING,
        ]);
    }

    /**
     * Start a session (builds queue and marks in progress).
     */
    public function startSession(CollectionSession $session, User $user): void
    {
        // Build the queue
        $this->buildQueue($session);

        // Start the session
        $session->start($user->id);
    }

    /**
     * Build the contact queue for a session.
     */
    public function buildQueue(CollectionSession $session): void
    {
        $collection = $session->collection;
        $contacts = $this->getEligibleContacts($collection);
        $prioritized = $this->prioritizeContacts($contacts, $collection);

        $position = 1;
        foreach ($prioritized as $item) {
            // Create entry
            $entry = CollectionEntry::create([
                'collection_id' => $collection->id,
                'session_id' => $session->id,
                'contact_type' => get_class($item['contact']),
                'contact_id' => $item['contact']->id,
                'status' => CollectionEntry::STATUS_PENDING,
                'input_mode' => $collection->format_mode === Collection::FORMAT_GRID
                    ? CollectionEntry::MODE_GRID
                    : CollectionEntry::MODE_FORM,
            ]);

            // Create queue item
            CollectionQueueItem::create([
                'session_id' => $session->id,
                'entry_id' => $entry->id,
                'contact_type' => get_class($item['contact']),
                'contact_id' => $item['contact']->id,
                'position' => $position++,
                'status' => CollectionQueueItem::STATUS_PENDING,
                'priority' => $item['priority'],
                'priority_reason' => $item['priority_reason'],
            ]);
        }

        $session->update(['total_contacts' => count($prioritized)]);
    }

    /**
     * Get eligible contacts based on collection scope.
     */
    protected function getEligibleContacts(Collection $collection): SupportCollection
    {
        $scope = $collection->contact_scope ?? [];
        $targetType = $scope['target_type'] ?? 'students';

        if ($targetType === 'students') {
            $query = Student::where('org_id', $collection->org_id)
                ->whereNull('deleted_at');

            // Filter by grades
            if (! empty($scope['grades'])) {
                $query->whereIn('grade_level', $scope['grades']);
            }

            // Filter by classrooms (homeroom)
            if (! empty($scope['classrooms'])) {
                $query->whereIn('homeroom_classroom_id', $scope['classrooms']);
            }

            // Filter by tags
            if (! empty($scope['tags'])) {
                $query->where(function ($q) use ($scope) {
                    foreach ($scope['tags'] as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }

            return $query->get();
        }

        // For user-based collections (parents, staff)
        if ($targetType === 'users') {
            $query = User::where('org_id', $collection->org_id);

            if (! empty($scope['roles'])) {
                $query->whereIn('role', $scope['roles']);
            }

            return $query->get();
        }

        return collect();
    }

    /**
     * Prioritize contacts based on risk level and other factors.
     */
    protected function prioritizeContacts(SupportCollection $contacts, Collection $collection): array
    {
        $prioritized = [];

        foreach ($contacts as $contact) {
            $priority = CollectionQueueItem::PRIORITY_NORMAL;
            $reason = null;

            // High-risk students get highest priority
            if ($contact instanceof Student) {
                if ($contact->risk_level === 'high') {
                    $priority = CollectionQueueItem::PRIORITY_CRITICAL;
                    $reason = 'High risk student';
                } elseif ($contact->risk_level === 'medium') {
                    $priority = CollectionQueueItem::PRIORITY_HIGH;
                    $reason = 'Medium risk student';
                }

                // Check for recent flags
                if ($contact->has_recent_flags ?? false) {
                    $priority = max($priority, CollectionQueueItem::PRIORITY_HIGH);
                    $reason = $reason ?? 'Recent concern flagged';
                }
            }

            $prioritized[] = [
                'contact' => $contact,
                'priority' => $priority,
                'priority_reason' => $reason,
            ];
        }

        // Sort by priority (descending), then by name
        usort($prioritized, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] - $a['priority'];
            }

            $nameA = $a['contact']->full_name ?? $a['contact']->name ?? '';
            $nameB = $b['contact']->full_name ?? $b['contact']->name ?? '';

            return strcmp($nameA, $nameB);
        });

        return $prioritized;
    }

    /**
     * Get the next contact in the queue.
     */
    public function getNextInQueue(CollectionSession $session): ?CollectionQueueItem
    {
        return $session->getNextQueueItem();
    }

    /**
     * Advance to the next contact in the queue.
     */
    public function advanceQueue(CollectionSession $session, CollectionEntry $completedEntry): ?CollectionQueueItem
    {
        // Mark current queue item as completed
        $currentItem = $completedEntry->queueItem;
        if ($currentItem) {
            $currentItem->markCompleted();
        }

        // Update session stats
        $session->updateStats();

        // Get next item
        return $this->getNextInQueue($session);
    }

    /**
     * Skip a contact in the queue.
     */
    public function skipInQueue(CollectionSession $session, CollectionEntry $entry, ?string $reason = null): ?CollectionQueueItem
    {
        // Mark entry as skipped
        $entry->skip($reason);

        // Mark queue item as skipped
        $queueItem = $entry->queueItem;
        if ($queueItem) {
            $queueItem->markSkipped();
        }

        // Update session stats
        $session->updateStats();

        // Get next item
        return $this->getNextInQueue($session);
    }

    /**
     * Complete a session.
     */
    public function completeSession(CollectionSession $session): void
    {
        $session->complete();
    }

    /**
     * Record a response for an entry.
     */
    public function recordResponse(CollectionEntry $entry, string $questionId, $response): void
    {
        $entry->recordResponse($questionId, $response);
    }

    /**
     * Complete an entry.
     */
    public function completeEntry(CollectionEntry $entry, ?array $scores = null, ?array $flags = null): void
    {
        $entry->complete($scores, $flags);
    }

    /**
     * Get questions for a collection.
     */
    public function getQuestions(Collection $collection): array
    {
        return $collection->getQuestions();
    }

    /**
     * Get statistics for a collection.
     */
    public function getStats(Collection $collection): array
    {
        $sessions = $collection->sessions();
        $entries = $collection->entries();

        return [
            'total_sessions' => $sessions->count(),
            'completed_sessions' => $sessions->where('status', CollectionSession::STATUS_COMPLETED)->count(),
            'total_entries' => $entries->count(),
            'completed_entries' => $entries->where('status', CollectionEntry::STATUS_COMPLETED)->count(),
            'average_completion_rate' => $sessions
                ->where('status', CollectionSession::STATUS_COMPLETED)
                ->avg('completion_rate') ?? 0,
            'last_session_date' => $sessions->latest('session_date')->first()?->session_date,
            'next_scheduled' => $collection->getActiveSchedule()?->next_scheduled_at,
        ];
    }
}
