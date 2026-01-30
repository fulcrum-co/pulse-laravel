<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;

class ContactList extends Model
{
    use SoftDeletes;

    // List types
    const TYPE_STUDENT = 'student';
    const TYPE_TEACHER = 'teacher';
    const TYPE_MIXED = 'mixed';

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
     * Get all students in this list.
     */
    public function students(): MorphToMany
    {
        return $this->morphedByMany(Student::class, 'contact', 'contact_list_members')
            ->withPivot('added_at', 'added_by')
            ->withTimestamps();
    }

    /**
     * Get all users (teachers) in this list.
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

        return $this->students()->count() + $this->users()->count();
    }

    /**
     * Get all contacts (students and/or users based on list type).
     */
    public function getAllMembers(): \Illuminate\Support\Collection
    {
        if ($this->is_dynamic) {
            return $this->getContactsQuery()->get();
        }

        $members = collect();

        if (in_array($this->list_type, [self::TYPE_STUDENT, self::TYPE_MIXED])) {
            $members = $members->merge($this->students);
        }

        if (in_array($this->list_type, [self::TYPE_TEACHER, self::TYPE_MIXED])) {
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

        if ($this->list_type === self::TYPE_STUDENT) {
            return $this->buildStudentQuery($criteria);
        } elseif ($this->list_type === self::TYPE_TEACHER) {
            return $this->buildTeacherQuery($criteria);
        }

        // For mixed, we'd need to handle differently
        return Student::where('org_id', $this->org_id);
    }

    /**
     * Build student query from filter criteria.
     */
    protected function buildStudentQuery(array $criteria): Builder
    {
        $query = Student::where('org_id', $this->org_id)
            ->whereNull('deleted_at');

        // Grade level filter
        if (!empty($criteria['grade_levels'])) {
            $query->whereIn('grade_level', $criteria['grade_levels']);
        }

        // Risk level filter
        if (!empty($criteria['risk_levels'])) {
            $query->whereIn('risk_level', $criteria['risk_levels']);
        }

        // Classroom filter
        if (!empty($criteria['classroom_ids'])) {
            $query->whereIn('homeroom_classroom_id', $criteria['classroom_ids']);
        }

        // IEP status filter
        if (isset($criteria['has_iep'])) {
            $query->where('iep_status', $criteria['has_iep']);
        }

        // ELL status filter
        if (isset($criteria['is_ell'])) {
            $query->where('ell_status', $criteria['is_ell']);
        }

        // Enrollment status filter
        if (!empty($criteria['enrollment_status'])) {
            $query->where('enrollment_status', $criteria['enrollment_status']);
        }

        // Tags filter
        if (!empty($criteria['tags'])) {
            foreach ($criteria['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        // Counselor filter
        if (!empty($criteria['counselor_ids'])) {
            $query->whereIn('counselor_user_id', $criteria['counselor_ids']);
        }

        return $query;
    }

    /**
     * Build teacher/user query from filter criteria.
     */
    protected function buildTeacherQuery(array $criteria): Builder
    {
        $query = User::where('org_id', $this->org_id)
            ->whereNull('deleted_at');

        // Role filter
        if (!empty($criteria['roles'])) {
            $query->whereIn('role', $criteria['roles']);
        }

        // Department filter (if using custom field)
        if (!empty($criteria['departments'])) {
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
        if (!$this->is_dynamic || empty($this->filter_criteria)) {
            return;
        }

        // For dynamic lists, members are always computed on-the-fly
        // This method exists for compatibility and could be used
        // to cache results if needed for performance
    }

    /**
     * Add a student to the list.
     */
    public function addStudent(Student $student, ?int $addedBy = null): void
    {
        if ($this->list_type === self::TYPE_TEACHER) {
            throw new \InvalidArgumentException('Cannot add student to teacher-only list');
        }

        $this->students()->syncWithoutDetaching([
            $student->id => [
                'added_at' => now(),
                'added_by' => $addedBy,
            ]
        ]);
    }

    /**
     * Add a user/teacher to the list.
     */
    public function addUser(User $user, ?int $addedBy = null): void
    {
        if ($this->list_type === self::TYPE_STUDENT) {
            throw new \InvalidArgumentException('Cannot add teacher to student-only list');
        }

        $this->users()->syncWithoutDetaching([
            $user->id => [
                'added_at' => now(),
                'added_by' => $addedBy,
            ]
        ]);
    }

    /**
     * Remove a student from the list.
     */
    public function removeStudent(Student $student): void
    {
        $this->students()->detach($student->id);
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
    public function addContacts(array $studentIds = [], array $userIds = [], ?int $addedBy = null): void
    {
        if (!empty($studentIds) && $this->list_type !== self::TYPE_TEACHER) {
            $syncData = [];
            foreach ($studentIds as $id) {
                $syncData[$id] = ['added_at' => now(), 'added_by' => $addedBy];
            }
            $this->students()->syncWithoutDetaching($syncData);
        }

        if (!empty($userIds) && $this->list_type !== self::TYPE_STUDENT) {
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
        if ($contact instanceof Student) {
            if ($this->is_dynamic) {
                return $this->getContactsQuery()->where('id', $contact->id)->exists();
            }
            return $this->students()->where('students.id', $contact->id)->exists();
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
