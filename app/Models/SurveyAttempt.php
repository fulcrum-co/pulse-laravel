<?php

namespace App\Models;

use App\Events\SurveyCompleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAttempt extends Model
{
    protected $fillable = [
        'survey_id',
        'student_id',
        'user_id',
        'status',
        'response_channel',
        'delivery_id',
        'responses',
        'results',
        'overall_score',
        'risk_level',
        'ai_analysis',
        'voice_recordings',
        'transcriptions',
        'conversation_log',
        'raw_responses',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected $casts = [
        'responses' => 'array',
        'results' => 'array',
        'ai_analysis' => 'array',
        'voice_recordings' => 'array',
        'transcriptions' => 'array',
        'conversation_log' => 'array',
        'raw_responses' => 'array',
        'overall_score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    /**
     * Response channel constants
     */
    public const CHANNEL_WEB = 'web';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_VOICE = 'voice';

    public const CHANNEL_CHAT = 'chat';

    /**
     * Get the survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who took the survey.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the delivery record.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(SurveyDelivery::class, 'delivery_id');
    }

    /**
     * Check if this attempt is high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->risk_level === 'high';
    }

    /**
     * Scope to filter completed attempts.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by risk level.
     */
    public function scopeRiskLevel(Builder $query, string $level): Builder
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope to filter high risk attempts.
     */
    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Scope to filter by response channel.
     */
    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('response_channel', $channel);
    }

    /**
     * Scope to filter in-progress attempts.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Record a response for a specific question.
     */
    public function recordResponse(string $questionId, mixed $response): void
    {
        $responses = $this->responses ?? [];
        $responses[$questionId] = $response;
        $this->update(['responses' => $responses]);
    }

    /**
     * Record a voice response.
     */
    public function recordVoiceResponse(string $questionId, string $audioPath, ?string $transcription = null): void
    {
        $voiceRecordings = $this->voice_recordings ?? [];
        $voiceRecordings[$questionId] = $audioPath;

        $transcriptions = $this->transcriptions ?? [];
        if ($transcription) {
            $transcriptions[$questionId] = $transcription;
        }

        $this->update([
            'voice_recordings' => $voiceRecordings,
            'transcriptions' => $transcriptions,
        ]);
    }

    /**
     * Add to conversation log.
     */
    public function addToConversationLog(string $role, string $content): void
    {
        $log = $this->conversation_log ?? [];
        $log[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['conversation_log' => $log]);
    }

    /**
     * Mark attempt as completed and fire event.
     */
    public function markCompleted(): void
    {
        $survey = $this->survey;

        // Calculate score if survey has interpretation config
        $score = $survey->calculateScore($this->responses ?? []);
        $riskLevel = $score !== null ? $survey->determineRiskLevel($score) : null;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at
                ? now()->diffInSeconds($this->started_at)
                : null,
            'overall_score' => $score,
            'risk_level' => $riskLevel,
        ]);

        // Fire completion event for workflow integration
        SurveyCompleted::dispatch($this);
    }

    /**
     * Mark attempt as abandoned.
     */
    public function markAbandoned(): void
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the respondent (student or user).
     */
    public function getRespondent(): ?Model
    {
        return $this->student ?? $this->user;
    }

    /**
     * Get respondent name.
     */
    public function getRespondentNameAttribute(): string
    {
        if ($this->student) {
            return $this->student->name ?? $this->student->first_name ?? 'Student';
        }
        if ($this->user) {
            return $this->user->name ?? 'User';
        }

        return 'Anonymous';
    }

    /**
     * Check if attempt is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if attempt is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Get response for a specific question.
     */
    public function getResponse(string $questionId): mixed
    {
        return $this->responses[$questionId] ?? null;
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ABANDONED => 'Abandoned',
        ];
    }

    /**
     * Get all available response channels.
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_WEB => 'Web Form',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_VOICE => 'Voice Call',
            self::CHANNEL_CHAT => 'Conversational Chat',
        ];
    }
}
