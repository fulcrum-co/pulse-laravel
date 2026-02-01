<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Conversation extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'conversations';

    protected $fillable = [
        'user_id',
        'org_id',
        'channel',
        'direction',
        'sinch_conversation_id',
        'phone_number',
        'call_duration_seconds',
        'recording_url',
        'messages',
        'related_survey_id',
        'related_attempt_id',
        'students_covered',
        'full_transcript',
        'llm_processed',
        'llm_summary',
        'status',
        'cost_usd',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'messages' => 'array',
        'students_covered' => 'array',
        'llm_processed' => 'boolean',
        'call_duration_seconds' => 'integer',
        'cost_usd' => 'float',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the related survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'related_survey_id');
    }

    /**
     * Get the related survey attempt.
     */
    public function surveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class, 'related_attempt_id');
    }

    /**
     * Get duration in human-readable format.
     */
    public function getDurationForHumansAttribute(): string
    {
        $seconds = $this->call_duration_seconds;

        if (! $seconds) {
            return 'N/A';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(string $direction, string $text): void
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'message_id' => (string) new \MongoDB\BSON\ObjectId,
            'direction' => $direction,
            'text' => $text,
            'timestamp' => now()->toISOString(),
            'delivery_status' => 'delivered',
        ];
        $this->update(['messages' => $messages]);
    }

    /**
     * Scope to filter by channel.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to filter voice calls.
     */
    public function scopeVoiceCalls($query)
    {
        return $query->where('channel', 'voice');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter completed conversations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
