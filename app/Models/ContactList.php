<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactList extends Model
{
    use SoftDeletes;

    // List types
    const TYPE_PARTICIPANT = 'participant';

    const TYPE_INSTRUCTOR = 'instructor';

    const TYPE_MIXED = 'mixed';

    // Legacy compatibility
    const TYPE_STUDENT = self::TYPE_PARTICIPANT;
    const TYPE_TEACHER = self::TYPE_INSTRUCTOR;

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'list_type',
        'filter_criteria',
        'is_dynamic',
        'created_by',
    ];

    protected $casts = [
        'filter_criteria' => 'array',
        'is_dynamic' => 'boolean',
    ];

    /**
     * Get the organization that owns this list.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this list.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all participants in this list.
     */
    public function participants(): MorphToMany
    {
        return $this->morphedByMany(Participant::class, 'contact', 'contact_list_members')
            ->withPivot('added_at', 'added_by')
            ->withTimestamps();
    }

    /**
     * Get all users (instructors) in this list.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'contact', 'contact_list_members')
            ->withPivot('added_at', 'added_by')
            ->withTimestamps();
    }

    /**
     * Get the count of all members.
     */
    public function getMemberCountAttribute(): int
    {
        if ($this->is_dynamic) {
            return $this->getContactsQuery()->count();
        }

        return $this->participants()->count() + $this->users()->count();
    }

    /**
     * Get all contacts (participants and/or users based on list type).
     */
    public function getAllMembers(): \Illuminate\Support\Collection
    {
        if ($this->is_dynamic) {
            return $this->getContactsQuery()->get();
        }

        $members = collect();

        if (in_array($this->list_type, [self::TYPE_PARTICIPANT, self::TYPE_MIXED])) {
            $members = $members->merge($this->participants);
        }

        if (in_array($this->list_type, [self::TYPE_INSTRUCTOR, self::TYPE_MIXED])) {
            $members = $members->merge($this->users);
        }

        return $members;
    }

    /**
     * Build query for dynamic list members.
     */
    public function getContactsQuery(): Builder
    {
        $criteria = $this->filter_criteria ?? [];

        if ($this->list_type === self::TYPE_PARTICIPANT) {
            return $this->buildParticipantQuery($criteria);
        } elseif ($this->list_type === self::TYPE_INSTRUCTOR) {
            return $this->buildInstructorQuery($criteria);
        }

        // For mixed, we'd need to handle differently
        return Participant::where('org_id', $this->org_id);
    }

    /**
     * Build participant query from filter criteria.
     */
    protected function buildParticipantQuery(array $criteria): Builder
    {
        $query = Participant::where('org_id', $this->org_id)
            ->whereNull('deleted_at');

        // Level level filter
        if (! empty($criteria['levels'])) {
            $query->whereIn('level', $criteria['levels']);
        }

        // Risk level filter
        if (! empty($criteria['risk_levels'])) {
            $query->whereIn('risk_level', $criteria['risk_levels']);
        }

        // LearningGroup filter
        if (! empty($criteria['learning_group_ids'])) {
            $query->whereIn('homeroom_learning_group_id', $criteria['learning_group_ids']);
        }

        // Support plan status filter
        if (isset($criteria['has_iep'])) {
            $query->where('iep_status', $criteria['has_iep']);
        }

        // Language support status filter
        if (isset($criteria['is_ell'])) {
            $query->where('ell_status', $criteria['is_ell']);
        }

        // Enrollment status filter
        if (! empty($criteria['enrollment_status'])) {
            $query->where('enrollment_status', $criteria['enrollment_status']);
        }

        // Tags filter
        if (! empty($criteria['tags'])) {
            foreach ($criteria['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        // Support Person filter
        if (! empty($criteria['support_person_ids'])) {
            $query->whereIn('support_person_user_id', $criteria['support_person_ids']);
        }

        return $query;
    }

    /**
     * Build instructor/user query from filter criteria.
     */
    protected function buildInstructorQuery(array $criteria): Builder
    {
        $query = User::where('org_id', $this->org_id)
            ->whereNull('deleted_at');

        // Role filter
        if (! empty($criteria['roles'])) {
            $query->whereIn('role', $criteria['roles']);
        }

        // Department filter (if using custom field)
        if (! empty($criteria['departments'])) {
            $query->where(function ($q) use ($criteria) {
                foreach ($criteria['departments'] as $dept) {
                    $q->orWhereJsonContains('metadata->department', $dept);
                }
            });
        }

        return $query;
    }

    /**
     * Refresh dynamic list members.
     * For dynamic lists, this is a no-op since members are computed.
     * For static lists being converted, this populates from current criteria.
     */
    public function refreshDynamicMembers(): void
    {
        if (! $this->is_dynamic || empty($this->filter_criteria)) {
            return;
        }

        // For dynamic lists, members are always computed on-the-fly
        // This method exists for compatibility and could be used
        // to cache results if needed for performance
    }

    /**
     * Add a participant to the list.
     */
    public function addLearner(Participant $participant, ?int $addedBy = null): void
    {
        if ($this->list_type === self::TYPE_TEACHER) {
            throw new \InvalidArgumentException('Cannot add participant to instructor-only list');
        }

        $this->participants()->syncWithoutDetaching([
            $participant->id => [
                'added_at' => now(),
                'added_by' => $addedBy,
            ],
        ]);
    }

    /**
     * Add a user/instructor to the list.
     */
    public function addUser(User $user, ?int $addedBy = null): void
    {
        if ($this->list_type === self::TYPE_STUDENT) {
            throw new \InvalidArgumentException('Cannot add instructor to participant-only list');
        }

        $this->users()->syncWithoutDetaching([
            $user->id => [
                'added_at' => now(),
                'added_by' => $addedBy,
            ],
        ]);
    }

    /**
     * Remove a participant from the list.
     */
    public function removeLearner(Participant $participant): void
    {
        $this->participants()->detach($participant->id);
    }

    /**
     * Remove a user from the list.
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    /**
     * Add multiple contacts at once.
     */
    public function addContacts(array $participantIds = [], array $userIds = [], ?int $addedBy = null): void
    {
        if (! empty($participantIds) && $this->list_type !== self::TYPE_TEACHER) {
            $syncData = [];
            foreach ($participantIds as $id) {
                $syncData[$id] = ['added_at' => now(), 'added_by' => $addedBy];
            }
            $this->participants()->syncWithoutDetaching($syncData);
        }

        if (! empty($userIds) && $this->list_type !== self::TYPE_STUDENT) {
            $syncData = [];
            foreach ($userIds as $id) {
                $syncData[$id] = ['added_at' => now(), 'added_by' => $addedBy];
            }
            $this->users()->syncWithoutDetaching($syncData);
        }
    }

    /**
     * Check if a contact is in this list.
     */
    public function hasContact($contact): bool
    {
        if ($contact instanceof Participant) {
            if ($this->is_dynamic) {
                return $this->getContactsQuery()->where('id', $contact->id)->exists();
            }

            return $this->participants()->where('participants.id', $contact->id)->exists();
        }

        if ($contact instanceof User) {
            if ($this->is_dynamic) {
                return $this->getContactsQuery()->where('id', $contact->id)->exists();
            }

            return $this->users()->where('users.id', $contact->id)->exists();
        }

        return false;
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter by list type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('list_type', $type);
    }

    /**
     * Scope for dynamic lists only.
     */
    public function scopeDynamic($query)
    {
        return $query->where('is_dynamic', true);
    }

    /**
     * Scope for static lists only.
     */
    public function scopeStatic($query)
    {
        return $query->where('is_dynamic', false);
    }
}
