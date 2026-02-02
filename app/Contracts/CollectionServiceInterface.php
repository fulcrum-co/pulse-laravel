<?php

namespace App\Contracts;

use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Interface for Collection Service operations.
 *
 * Defines the contract for managing learning content collections,
 * including CRUD operations, entry management, and smart collection features.
 */
interface CollectionServiceInterface
{
    /**
     * Create a new collection.
     *
     * @param array $data Collection data (name, description, type, rules, etc.)
     * @param User $user The user creating the collection
     * @param Organization $organization The organization the collection belongs to
     * @return Collection The created collection
     */
    public function create(array $data, User $user, Organization $organization): Collection;

    /**
     * Update an existing collection.
     *
     * @param Collection $collection The collection to update
     * @param array $data Updated data
     * @return Collection The updated collection
     */
    public function update(Collection $collection, array $data): Collection;

    /**
     * Delete a collection (soft delete).
     *
     * @param Collection $collection The collection to delete
     * @return bool Whether deletion was successful
     */
    public function delete(Collection $collection): bool;

    /**
     * Add an entry to a collection.
     *
     * @param Collection $collection The collection to add to
     * @param Model $content The content model (MiniCourse, Resource, etc.)
     * @param array $metadata Optional metadata for the entry
     * @return CollectionEntry The created entry
     * @throws \App\Exceptions\DuplicateEntryException If entry already exists
     */
    public function addEntry(Collection $collection, Model $content, array $metadata = []): CollectionEntry;

    /**
     * Remove an entry from a collection.
     *
     * @param CollectionEntry $entry The entry to remove
     * @return bool Whether removal was successful
     */
    public function removeEntry(CollectionEntry $entry): bool;

    /**
     * Reorder entries in a collection.
     *
     * @param Collection $collection The collection
     * @param array $orderedIds Array of entry IDs in desired order
     * @return void
     */
    public function reorderEntries(Collection $collection, array $orderedIds): void;

    /**
     * Get all collections accessible to a user.
     *
     * @param User $user The user
     * @param Organization $organization The organization context
     * @param array $filters Optional filters (type, is_public, etc.)
     * @return SupportCollection Collection of Collection models
     */
    public function getForUser(User $user, Organization $organization, array $filters = []): SupportCollection;

    /**
     * Refresh a smart collection's entries based on its rules.
     *
     * @param Collection $collection The smart collection to refresh
     * @return int Number of entries after refresh
     */
    public function refreshSmartCollection(Collection $collection): int;

    /**
     * Get statistics for a collection.
     *
     * @param Collection $collection The collection
     * @return array Stats including total_items, total_duration, counts by type
     */
    public function getStats(Collection $collection): array;

    /**
     * Duplicate a collection with its entries.
     *
     * @param Collection $collection The collection to duplicate
     * @param User $user The user who will own the duplicate
     * @return Collection The duplicated collection
     */
    public function duplicate(Collection $collection, User $user): Collection;
}
