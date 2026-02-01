<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CollectionEntry extends Model
{
    protected $fillable = [
        'collection_id',
        'session_id',
        'contact_type',
        'contact_id',
        'collected_by_user_id',
        'status',
        'input_mode',
        'responses',
        'voice_recordings',
        'transcriptions',
        'ai_conversation_log',
        'raw_input',
        'computed_scores',
        'flags',
        'duration_seconds',
        'started_at',
        'completed_at',
        'skip_reason',
    ];

    protected $casts = [
        'responses' => 'array',
        'voice_recordings' => 'array',
        'transcriptions' => 'array',
        'ai_conversation_log' => 'array',
        'raw_input' => 'array',
        'computed_scores' => 'array',
        'flags' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * Input mode constants
     */
    public const MODE_FORM = 'form';

    public const MODE_VOICE = 'voice';

    public const MODE_AI_CONVERSATION = 'ai_conversation';

    public const MODE_GRID = 'grid';

    /**
     * Get the collection that owns this entry.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the session that owns this entry.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CollectionSession::class, 'session_id');
    }

    /**
     * Get the user who collected this entry.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    /**
     * Get the contact (polymorphic).
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the queue item for this entry.
     */
    public function queueItem(): HasOne
    {
        return $this->hasOne(CollectionQueueItem::class, 'entry_id');
    }

    /**
     * Scope to filter by session.
     */
    public function scopeForSession(Builder $query, int $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Start the entry.
     */
    public function start(int $userId, ?string $mode = null): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'collected_by_user_id' => $userId,
            'input_mode' => $mode ?? $this->input_mode,
        ]);
    }

    /**
     * Record a response for a question.
     */
    public function recordResponse(string $questionId, $response): void
    {
        $responses = $this->responses ?? [];
        $responses[$questionId] = $response;
        $this->update(['responses' => $responses]);
    }

    /**
     * Add a voice recording.
     */
    public function addVoiceRecording(string $path): void
    {
        $recordings = $this->voice_recordings ?? [];
        $recordings[] = [
            'path' => $path,
            'recorded_at' => now()->toIso8601String(),
        ];
        $this->update(['voice_recordings' => $recordings]);
    }

    /**
     * Add a transcription.
     */
    public function addTranscription(string $text, ?string $questionId = null): void
    {
        $transcriptions = $this->transcriptions ?? [];
        $transcriptions[] = [
            'text' => $text,
            'question_id' => $questionId,
            'transcribed_at' => now()->toIso8601String(),
        ];
        $this->update(['transcriptions' => $transcriptions]);
    }

    /**
     * Add to AI conversation log.
     */
    public function addToConversationLog(string $role, string $content): void
    {
        $log = $this->ai_conversation_log ?? [];
        $log[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['ai_conversation_log' => $log]);
    }

    /**
     * Complete the entry.
     */
    public function complete(?array $scores = null, ?array $flags = null): void
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ];

        if ($this->started_at) {
            $data['duration_seconds'] = now()->diffInSeconds($this->started_at);
        }

        if ($scores !== null) {
            $data['computed_scores'] = $scores;
        }

        if ($flags !== null) {
            $data['flags'] = $flags;
        }

        $this->update($data);
    }

    /**
     * Skip the entry.
     */
    public function skip(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'completed_at' => now(),
            'skip_reason' => $reason,
        ]);
    }

    /**
     * Get the response for a specific question.
     */
    public function getResponse(string $questionId)
    {
        return $this->responses[$questionId] ?? null;
    }

    /**
     * Check if entry is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if entry was skipped.
     */
    public function wasSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    /**
     * Get statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    /**
     * Get input modes for dropdown.
     */
    public static function getInputModes(): array
    {
        return [
            self::MODE_FORM => 'Form',
            self::MODE_VOICE => 'Voice',
            self::MODE_AI_CONVERSATION => 'AI Conversation',
            self::MODE_GRID => 'Grid',
        ];
    }
}
