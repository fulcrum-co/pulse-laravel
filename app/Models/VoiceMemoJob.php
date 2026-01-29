<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceMemoJob extends Model
{
    protected $fillable = [
        'contact_note_id',
        'status',
        'provider',
        'external_job_id',
        'transcription_result',
        'extracted_data',
        'error_message',
        'retry_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'transcription_result' => 'array',
        'extracted_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status values
    public const STATUS_PENDING = 'pending';
    public const STATUS_UPLOADING = 'uploading';
    public const STATUS_TRANSCRIBING = 'transcribing';
    public const STATUS_EXTRACTING = 'extracting';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Provider options
    public const PROVIDER_WHISPER = 'whisper';
    public const PROVIDER_ASSEMBLY_AI = 'assembly_ai';

    /**
     * Get the associated contact note.
     */
    public function contactNote(): BelongsTo
    {
        return $this->belongsTo(ContactNote::class);
    }

    /**
     * Check if job is processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_UPLOADING,
            self::STATUS_TRANSCRIBING,
            self::STATUS_EXTRACTING,
        ]);
    }

    /**
     * Check if job is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if job failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark job as started.
     */
    public function markStarted(): void
    {
        $this->update([
            'status' => self::STATUS_TRANSCRIBING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark job as completed.
     */
    public function markCompleted(array $transcriptionResult, ?array $extractedData = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transcription_result' => $transcriptionResult,
            'extracted_data' => $extractedData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Scope to filter pending jobs.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter failed jobs that can be retried.
     */
    public function scopeRetryable($query, int $maxRetries = 3)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries);
    }
}
