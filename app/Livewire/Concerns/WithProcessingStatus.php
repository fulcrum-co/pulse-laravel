<?php

namespace App\Livewire\Concerns;

use App\Models\EmbeddingJob;
use Illuminate\Support\Collection;

/**
 * Trait for tracking processing status of resources with embeddings.
 * Used to show visual indicators when content is being processed.
 */
trait WithProcessingStatus
{
    /**
     * IDs of items currently being processed.
     */
    public array $processingIds = [];

    /**
     * Get processing status for a collection of models.
     *
     * @return array<int, string> Map of model ID to status ('pending', 'processing', 'completed', 'failed')
     */
    public function getProcessingStatuses(Collection $models, string $modelClass): array
    {
        if ($models->isEmpty()) {
            return [];
        }

        $statuses = [];
        $modelIds = $models->pluck('id')->toArray();

        // Get any pending/processing embedding jobs for these models
        $jobs = EmbeddingJob::where('embeddable_type', $modelClass)
            ->whereIn('embeddable_id', $modelIds)
            ->whereIn('status', ['pending', 'processing'])
            ->pluck('status', 'embeddable_id')
            ->toArray();

        foreach ($models as $model) {
            if (isset($jobs[$model->id])) {
                $statuses[$model->id] = $jobs[$model->id];
            } elseif (method_exists($model, 'hasEmbedding') && ! $model->hasEmbedding()) {
                // No embedding and no job - might need generation
                $statuses[$model->id] = 'needs_embedding';
            } elseif (method_exists($model, 'needsEmbeddingUpdate') && $model->needsEmbeddingUpdate()) {
                // Content changed since embedding was generated
                $statuses[$model->id] = 'stale';
            } else {
                $statuses[$model->id] = 'ready';
            }
        }

        return $statuses;
    }

    /**
     * Check if a specific item is being processed.
     */
    public function isProcessing(int $id): bool
    {
        return in_array($id, $this->processingIds);
    }

    /**
     * Get the display status for a model.
     */
    public function getDisplayStatus(string $status): array
    {
        return match ($status) {
            'pending' => [
                'label' => 'Queued',
                'color' => 'yellow',
                'icon' => 'clock',
                'animate' => false,
            ],
            'processing' => [
                'label' => 'Processing',
                'color' => 'blue',
                'icon' => 'arrow-path',
                'animate' => true,
            ],
            'needs_embedding' => [
                'label' => 'Indexing',
                'color' => 'gray',
                'icon' => 'sparkles',
                'animate' => true,
            ],
            'stale' => [
                'label' => 'Updating',
                'color' => 'orange',
                'icon' => 'arrow-path',
                'animate' => true,
            ],
            'failed' => [
                'label' => 'Failed',
                'color' => 'red',
                'icon' => 'exclamation-circle',
                'animate' => false,
            ],
            default => [
                'label' => 'Ready',
                'color' => 'green',
                'icon' => 'check-circle',
                'animate' => false,
            ],
        };
    }

    /**
     * Refresh processing status for tracked items.
     * Call this via polling or after actions.
     */
    public function refreshProcessingStatus(): void
    {
        if (empty($this->processingIds)) {
            return;
        }

        // Check if any previously processing items are now complete
        $stillProcessing = EmbeddingJob::whereIn('embeddable_id', $this->processingIds)
            ->whereIn('status', ['pending', 'processing'])
            ->pluck('embeddable_id')
            ->toArray();

        // Remove completed items from tracking
        $this->processingIds = $stillProcessing;

        // Emit event if all processing is complete
        if (empty($this->processingIds)) {
            $this->dispatch('processing-complete');
        }
    }

    /**
     * Get count of items currently processing.
     */
    public function getProcessingCountProperty(): int
    {
        return count($this->processingIds);
    }
}
