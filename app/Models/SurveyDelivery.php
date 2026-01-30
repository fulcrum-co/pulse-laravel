<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class SurveyDelivery extends Model
{
    protected $fillable = [
        'survey_id',
        'survey_attempt_id',
        'channel',
        'status',
        'recipient_type',
        'recipient_id',
        'phone_number',
        'external_id',
        'delivery_metadata',
        'response_data',
        'current_question_index',
        'scheduled_for',
        'delivered_at',
        'completed_at',
    ];

    protected $casts = [
        'delivery_metadata' => 'array',
        'response_data' => 'array',
        'scheduled_for' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Channel constants
     */
    public const CHANNEL_WEB = 'web';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_VOICE = 'voice_call';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_CHAT = 'chat';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the survey attempt.
     */
    public function surveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class);
    }

    /**
     * Get the recipient (polymorphic - student, user, etc.).
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope by channel.
     */
    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope by status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pending deliveries.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope in-progress deliveries.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope by external ID (Sinch message/call ID).
     */
    public function scopeByExternalId(Builder $query, string $externalId): Builder
    {
        return $query->where('external_id', $externalId);
    }

    /**
     * Scope by phone number.
     */
    public function scopeByPhone(Builder $query, string $phoneNumber): Builder
    {
        return $query->where('phone_number', $phoneNumber);
    }

    /**
     * Scope scheduled deliveries ready to send.
     */
    public function scopeReadyToSend(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where(function ($q) {
                $q->whereNull('scheduled_for')
                  ->orWhere('scheduled_for', '<=', now());
            });
    }

    /**
     * Mark as sent.
     */
    public function markSent(string $externalId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'external_id' => $externalId,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as in progress.
     */
    public function markInProgress(): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(int $surveyAttemptId = null): void
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ];
        if ($surveyAttemptId) {
            $data['survey_attempt_id'] = $surveyAttemptId;
        }
        $this->update($data);
    }

    /**
     * Mark as failed.
     */
    public function markFailed(string $reason = null): void
    {
        $metadata = $this->delivery_metadata ?? [];
        $metadata['failure_reason'] = $reason;
        $this->update([
            'status' => self::STATUS_FAILED,
            'delivery_metadata' => $metadata,
        ]);
    }

    /**
     * Record a response for the current question.
     */
    public function recordResponse(mixed $response): void
    {
        $responses = $this->response_data ?? [];
        $responses['q' . ($this->current_question_index + 1)] = $response;
        $this->update(['response_data' => $responses]);
    }

    /**
     * Advance to next question.
     */
    public function advanceQuestion(): void
    {
        $this->increment('current_question_index');
    }

    /**
     * Get current question from survey.
     */
    public function getCurrentQuestion(): ?array
    {
        $questions = $this->survey->questions ?? [];
        return $questions[$this->current_question_index] ?? null;
    }

    /**
     * Check if all questions have been answered.
     */
    public function isComplete(): bool
    {
        $totalQuestions = count($this->survey->questions ?? []);
        return $this->current_question_index >= $totalQuestions;
    }

    /**
     * Get the recipient's display name.
     */
    public function getRecipientNameAttribute(): string
    {
        if ($this->recipient) {
            return $this->recipient->name ?? $this->recipient->first_name ?? 'Unknown';
        }
        return $this->phone_number ?? 'Unknown';
    }

    /**
     * Format phone number for display.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone_number) {
            return null;
        }
        // Simple formatting - can be enhanced
        $number = preg_replace('/[^0-9]/', '', $this->phone_number);
        if (strlen($number) === 11 && str_starts_with($number, '1')) {
            return sprintf('+1 (%s) %s-%s',
                substr($number, 1, 3),
                substr($number, 4, 3),
                substr($number, 7, 4)
            );
        }
        return $this->phone_number;
    }

    /**
     * Get all available channels.
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_WEB => 'Web Form',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_VOICE => 'Voice Call',
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_CHAT => 'Conversational Chat',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
