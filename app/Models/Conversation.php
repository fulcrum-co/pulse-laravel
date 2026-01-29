<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'student_id',
        'survey_attempt_id',
        'conversation_type',
        'status',
        'messages',
        'ai_summary',
        'detected_patterns',
        'sentiment',
        'sentiment_score',
        'requires_follow_up',
        'flagged_for_review',
        'flag_reason',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'messages' => 'array',
        'ai_summary' => 'array',
        'detected_patterns' => 'array',
        'sentiment_score' => 'decimal:2',
        'requires_follow_up' => 'boolean',
        'flagged_for_review' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the related survey attempt.
     */
    public function surveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class);
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toISOString(),
        ];
        $this->update(['messages' => $messages]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter flagged conversations.
     */
    public function scopeFlagged($query)
    {
        return $query->where('flagged_for_review', true);
    }

    /**
     * Scope to filter completed conversations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
