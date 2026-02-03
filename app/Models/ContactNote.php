<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'contact_type',
        'contact_id',
        'note_type',
        'content',
        'raw_content',
        'structured_data',
        'is_voice_memo',
        'audio_file_path',
        'audio_disk',
        'audio_duration_seconds',
        'transcription',
        'transcription_status',
        'transcription_provider',
        'transcribed_at',
        'is_private',
        'visibility',
        'visible_to_roles',
        'parent_note_id',
        'related_plan_id',
        'related_survey_attempt_id',
        'contains_pii',
        'requires_consent_for_share',
        'created_by',
    ];

    protected $casts = [
        'structured_data' => 'array',
        'visible_to_roles' => 'array',
        'is_voice_memo' => 'boolean',
        'is_private' => 'boolean',
        'contains_pii' => 'boolean',
        'requires_consent_for_share' => 'boolean',
        'transcribed_at' => 'datetime',
    ];

    // Note types
    public const TYPE_GENERAL = 'general';

    public const TYPE_FOLLOW_UP = 'follow_up';

    public const TYPE_CONCERN = 'concern';

    public const TYPE_MILESTONE = 'milestone';

    public const TYPE_VOICE_MEMO = 'voice_memo';

    public const TYPE_AI_SUMMARY = 'ai_summary';

    // Visibility options
    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_TEAM = 'team';

    public const VISIBILITY_ORGANIZATION = 'organization';

    // Transcription statuses
    public const TRANSCRIPTION_PENDING = 'pending';

    public const TRANSCRIPTION_PROCESSING = 'processing';

    public const TRANSCRIPTION_COMPLETED = 'completed';

    public const TRANSCRIPTION_FAILED = 'failed';

    /**
     * Get the contact (Learner or User).
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the author of the note.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent note (for replies).
     */
    public function parentNote(): BelongsTo
    {
        return $this->belongsTo(ContactNote::class, 'parent_note_id');
    }

    /**
     * Get replies to this note.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ContactNote::class, 'parent_note_id');
    }

    /**
     * Get the related strategic plan.
     */
    public function relatedPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class, 'related_plan_id');
    }

    /**
     * Get the related survey attempt.
     */
    public function relatedSurveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class, 'related_survey_attempt_id');
    }

    /**
     * Get the voice memo processing job.
     */
    public function voiceMemoJob()
    {
        return $this->hasOne(VoiceMemoJob::class, 'contact_note_id');
    }

    /**
     * Check if a user can view this note.
     */
    public function isVisibleTo(User $user): bool
    {
        // Author can always see their own notes
        if ($this->created_by === $user->id) {
            return true;
        }

        // Private notes only visible to author
        if ($this->is_private) {
            return false;
        }

        // Check visibility level
        if ($this->visibility === self::VISIBILITY_ORGANIZATION && $user->org_id === $this->org_id) {
            return true;
        }

        // Check role-based visibility
        if ($this->visible_to_roles && in_array($user->primary_role, $this->visible_to_roles)) {
            return true;
        }

        return false;
    }

    /**
     * Scope to filter notes visible to a user.
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('is_private', false)
                        ->where('org_id', $user->org_id);
                });
        });
    }

    /**
     * Scope to filter by note type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('note_type', $type);
    }

    /**
     * Scope to filter voice memos.
     */
    public function scopeVoiceMemos($query)
    {
        return $query->where('is_voice_memo', true);
    }

    /**
     * Scope to filter by contact.
     */
    public function scopeForContact($query, string $type, int $id)
    {
        return $query->where('contact_type', $type)->where('contact_id', $id);
    }

    /**
     * Check if transcription is pending.
     */
    public function isTranscriptionPending(): bool
    {
        return $this->is_voice_memo &&
               in_array($this->transcription_status, [self::TRANSCRIPTION_PENDING, self::TRANSCRIPTION_PROCESSING]);
    }

    /**
     * Get formatted duration for voice memos.
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (! $this->audio_duration_seconds) {
            return null;
        }

        $minutes = floor($this->audio_duration_seconds / 60);
        $seconds = $this->audio_duration_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
