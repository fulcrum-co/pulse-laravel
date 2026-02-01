<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyCreationSession extends Model
{
    protected $fillable = [
        'org_id',
        'user_id',
        'survey_id',
        'creation_mode',
        'status',
        'conversation_history',
        'draft_questions',
        'ai_suggestions',
        'context',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'conversation_history' => 'array',
        'draft_questions' => 'array',
        'ai_suggestions' => 'array',
        'context' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Creation mode constants
     */
    public const MODE_CHAT = 'chat';

    public const MODE_VOICE = 'voice';

    public const MODE_STATIC = 'static';

    public const MODE_HYBRID = 'hybrid';

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who started this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the resulting survey (if finalized).
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Scope to get active sessions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get sessions by user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get sessions by creation mode.
     */
    public function scopeMode(Builder $query, string $mode): Builder
    {
        return $query->where('creation_mode', $mode);
    }

    /**
     * Add a message to conversation history.
     */
    public function addMessage(string $role, string $content): void
    {
        $history = $this->conversation_history ?? [];
        $history[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['conversation_history' => $history]);
    }

    /**
     * Add a draft question.
     */
    public function addDraftQuestion(array $question): void
    {
        $questions = $this->draft_questions ?? [];
        $question['id'] = $question['id'] ?? 'q'.(count($questions) + 1);
        $questions[] = $question;
        $this->update(['draft_questions' => $questions]);
    }

    /**
     * Update a draft question by index.
     */
    public function updateDraftQuestion(int $index, array $question): void
    {
        $questions = $this->draft_questions ?? [];
        if (isset($questions[$index])) {
            $questions[$index] = array_merge($questions[$index], $question);
            $this->update(['draft_questions' => $questions]);
        }
    }

    /**
     * Remove a draft question by index.
     */
    public function removeDraftQuestion(int $index): void
    {
        $questions = $this->draft_questions ?? [];
        array_splice($questions, $index, 1);
        // Re-index question IDs
        foreach ($questions as $i => $q) {
            $questions[$i]['id'] = 'q'.($i + 1);
        }
        $this->update(['draft_questions' => $questions]);
    }

    /**
     * Set AI suggestions.
     */
    public function setSuggestions(array $suggestions): void
    {
        $this->update(['ai_suggestions' => $suggestions]);
    }

    /**
     * Update context.
     */
    public function updateContext(array $context): void
    {
        $currentContext = $this->context ?? [];
        $this->update(['context' => array_merge($currentContext, $context)]);
    }

    /**
     * Mark session as completed.
     */
    public function markCompleted(int $surveyId): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'survey_id' => $surveyId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark session as abandoned.
     */
    public function abandon(): void
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if session is still active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the last message from conversation history.
     */
    public function getLastMessage(): ?array
    {
        $history = $this->conversation_history ?? [];

        return ! empty($history) ? end($history) : null;
    }

    /**
     * Get draft question count.
     */
    public function getDraftQuestionCountAttribute(): int
    {
        return count($this->draft_questions ?? []);
    }

    /**
     * Get all available creation modes.
     */
    public static function getCreationModes(): array
    {
        return [
            self::MODE_CHAT => 'AI Chat Assistant',
            self::MODE_VOICE => 'Voice Recording',
            self::MODE_STATIC => 'Form Builder',
            self::MODE_HYBRID => 'Hybrid Mode',
        ];
    }
}
