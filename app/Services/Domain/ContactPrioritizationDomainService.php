<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\CollectionQueueItem;
use App\Models\Learner;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Domain service for contact prioritization in collection queues.
 * Handles all business logic for assigning priority levels and sorting contacts.
 */
class ContactPrioritizationDomainService
{
    /**
     * Assign priority level to a contact based on risk and other factors.
     */
    public function assignPriority($contact): array
    {
        $priority = CollectionQueueItem::PRIORITY_NORMAL;
        $reason = null;

        // High-risk learners get highest priority
        if ($contact instanceof Learner) {
            if ($contact->risk_level === 'high') {
                $priority = CollectionQueueItem::PRIORITY_CRITICAL;
                $reason = 'High risk learner';
            } elseif ($contact->risk_level === 'medium') {
                $priority = CollectionQueueItem::PRIORITY_HIGH;
                $reason = 'Medium risk learner';
            }

            // Check for recent flags - escalate priority
            if ($contact->has_recent_flags ?? false) {
                $priority = max($priority, CollectionQueueItem::PRIORITY_HIGH);
                $reason = $reason ?? 'Recent concern flagged';
            }
        }

        return [
            'priority' => $priority,
            'reason' => $reason,
        ];
    }

    /**
     * Prioritize contacts based on risk level and other factors.
     * Returns sorted array with priority levels and reasons.
     */
    public function prioritizeContacts(SupportCollection $contacts): array
    {
        $prioritized = [];

        foreach ($contacts as $contact) {
            $assignment = $this->assignPriority($contact);

            $prioritized[] = [
                'contact' => $contact,
                'priority' => $assignment['priority'],
                'priority_reason' => $assignment['reason'],
            ];
        }

        // Sort by priority (descending), then by name
        return $this->sortByPriority($prioritized);
    }

    /**
     * Sort contacts by priority level and then alphabetically by name.
     */
    protected function sortByPriority(array $contacts): array
    {
        usort($contacts, function ($a, $b) {
            // First, sort by priority (highest first)
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] - $a['priority'];
            }

            // Then, sort alphabetically by name
            $nameA = $a['contact']->full_name ?? $a['contact']->name ?? '';
            $nameB = $b['contact']->full_name ?? $b['contact']->name ?? '';

            return strcmp($nameA, $nameB);
        });

        return $contacts;
    }

    /**
     * Get the priority level for a given risk level.
     */
    public function getPriorityForRiskLevel(string $riskLevel): int
    {
        return match ($riskLevel) {
            'critical' => CollectionQueueItem::PRIORITY_CRITICAL,
            'high' => CollectionQueueItem::PRIORITY_HIGH,
            'medium' => CollectionQueueItem::PRIORITY_MEDIUM,
            'low' => CollectionQueueItem::PRIORITY_LOW,
            default => CollectionQueueItem::PRIORITY_NORMAL,
        };
    }

    /**
     * Get the priority level name for display.
     */
    public function getPriorityName(int $priority): string
    {
        return match ($priority) {
            CollectionQueueItem::PRIORITY_CRITICAL => 'Critical',
            CollectionQueueItem::PRIORITY_HIGH => 'High',
            CollectionQueueItem::PRIORITY_MEDIUM => 'Medium',
            CollectionQueueItem::PRIORITY_LOW => 'Low',
            default => 'Normal',
        };
    }
}
