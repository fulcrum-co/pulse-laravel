<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiniCourseVersion extends Model
{
    protected $fillable = [
        'mini_course_id',
        'version_number',
        'title',
        'description',
        'objectives',
        'rationale',
        'expected_experience',
        'steps_snapshot',
        'change_summary',
        'created_by',
    ];

    protected $casts = [
        'objectives' => 'array',
        'steps_snapshot' => 'array',
    ];

    /**
     * The mini-course this version belongs to.
     */
    public function miniCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * Creator of this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Enrollments using this specific version.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(MiniCourseEnrollment::class);
    }

    /**
     * Get version label (e.g., "v1", "v2").
     */
    public function getVersionLabelAttribute(): string
    {
        return 'v' . $this->version_number;
    }

    /**
     * Check if this is the current version.
     */
    public function isCurrent(): bool
    {
        return $this->miniCourse->current_version_id === $this->id;
    }

    /**
     * Restore this version as the current course content.
     */
    public function restoreAsCurrent(): void
    {
        $course = $this->miniCourse;

        // Update course with this version's content
        $course->update([
            'title' => $this->title,
            'description' => $this->description,
            'objectives' => $this->objectives,
            'rationale' => $this->rationale,
            'expected_experience' => $this->expected_experience,
        ]);

        // Delete current steps
        $course->steps()->delete();

        // Recreate steps from snapshot
        if ($this->steps_snapshot) {
            foreach ($this->steps_snapshot as $index => $stepData) {
                $course->steps()->create([
                    'sort_order' => $index,
                    'step_type' => $stepData['step_type'] ?? 'content',
                    'title' => $stepData['title'] ?? '',
                    'description' => $stepData['description'] ?? null,
                    'instructions' => $stepData['instructions'] ?? null,
                    'content_type' => $stepData['content_type'] ?? 'text',
                    'content_data' => $stepData['content_data'] ?? null,
                    'resource_id' => $stepData['resource_id'] ?? null,
                    'provider_id' => $stepData['provider_id'] ?? null,
                    'program_id' => $stepData['program_id'] ?? null,
                    'estimated_duration_minutes' => $stepData['estimated_duration_minutes'] ?? null,
                    'is_required' => $stepData['is_required'] ?? true,
                    'completion_criteria' => $stepData['completion_criteria'] ?? null,
                    'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
                ]);
            }
        }

        // Create a new version recording this restoration
        $course->createVersion("Restored from {$this->version_label}");
    }

    /**
     * Get step count from snapshot.
     */
    public function getStepCountAttribute(): int
    {
        return count($this->steps_snapshot ?? []);
    }

    /**
     * Compare with another version.
     */
    public function compareWith(MiniCourseVersion $other): array
    {
        $changes = [];

        if ($this->title !== $other->title) {
            $changes['title'] = ['old' => $other->title, 'new' => $this->title];
        }

        if ($this->description !== $other->description) {
            $changes['description'] = ['old' => $other->description, 'new' => $this->description];
        }

        if ($this->objectives !== $other->objectives) {
            $changes['objectives'] = ['old' => $other->objectives, 'new' => $this->objectives];
        }

        if ($this->rationale !== $other->rationale) {
            $changes['rationale'] = ['old' => $other->rationale, 'new' => $this->rationale];
        }

        $thisStepCount = count($this->steps_snapshot ?? []);
        $otherStepCount = count($other->steps_snapshot ?? []);

        if ($thisStepCount !== $otherStepCount) {
            $changes['step_count'] = ['old' => $otherStepCount, 'new' => $thisStepCount];
        }

        return $changes;
    }
}
