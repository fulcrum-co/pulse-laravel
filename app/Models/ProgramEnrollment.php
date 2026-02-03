<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEnrollment extends Model
{
    protected $fillable = [
        'program_id',
        'learner_id',
        'enrolled_by',
        'status',
        'notes',
        'enrolled_at',
        'started_at',
        'completed_at',
        'progress_percent',
        'feedback',
    ];

    protected $casts = [
        'feedback' => 'array',
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the program.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the learner.
     */
    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }

    /**
     * Get the user who made the enrollment.
     */
    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    /**
     * Mark as active/started.
     */
    public function markStarted(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percent' => 100,
        ]);
    }

    /**
     * Update progress.
     */
    public function updateProgress(int $percent): void
    {
        $this->update(['progress_percent' => min(100, max(0, $percent))]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter active.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter completed.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
