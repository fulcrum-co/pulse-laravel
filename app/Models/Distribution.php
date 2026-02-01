<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribution extends Model
{
    use HasFactory, SoftDeletes;

    // Distribution types
    const TYPE_ONE_TIME = 'one_time';

    const TYPE_RECURRING = 'recurring';

    // Channels
    const CHANNEL_EMAIL = 'email';

    const CHANNEL_SMS = 'sms';

    // Statuses
    const STATUS_DRAFT = 'draft';

    const STATUS_SCHEDULED = 'scheduled';

    const STATUS_ACTIVE = 'active';

    const STATUS_PAUSED = 'paused';

    const STATUS_COMPLETED = 'completed';

    const STATUS_ARCHIVED = 'archived';

    // Content types
    const CONTENT_REPORT = 'report';

    const CONTENT_CUSTOM = 'custom';

    // Report modes
    const REPORT_MODE_STATIC = 'static';

    const REPORT_MODE_LIVE = 'live';

    // Recipient types
    const RECIPIENT_CONTACT_LIST = 'contact_list';

    const RECIPIENT_INDIVIDUAL = 'individual';

    const RECIPIENT_QUERY = 'query';

    protected $fillable = [
        'org_id',
        'title',
        'description',
        'distribution_type',
        'channel',
        'status',
        'content_type',
        'report_id',
        'report_mode',
        'subject',
        'message_body',
        'message_template_id',
        'recipient_type',
        'contact_list_id',
        'recipient_ids',
        'recipient_query',
        'scheduled_for',
        'timezone',
        'recurrence_config',
        'created_by',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'recipient_query' => 'array',
        'recurrence_config' => 'array',
        'scheduled_for' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CustomReport::class, 'report_id');
    }

    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function contactList(): BelongsTo
    {
        return $this->belongsTo(ContactList::class);
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(DistributionSchedule::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(DistributionDelivery::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePaused($query)
    {
        return $query->where('status', self::STATUS_PAUSED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRecurring($query)
    {
        return $query->where('distribution_type', self::TYPE_RECURRING);
    }

    public function scopeOneTime($query)
    {
        return $query->where('distribution_type', self::TYPE_ONE_TIME);
    }

    public function scopeEmail($query)
    {
        return $query->where('channel', self::CHANNEL_EMAIL);
    }

    public function scopeSms($query)
    {
        return $query->where('channel', self::CHANNEL_SMS);
    }

    // Helpers
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRecurring(): bool
    {
        return $this->distribution_type === self::TYPE_RECURRING;
    }

    public function isOneTime(): bool
    {
        return $this->distribution_type === self::TYPE_ONE_TIME;
    }

    public function usesReport(): bool
    {
        return $this->content_type === self::CONTENT_REPORT;
    }

    public function usesCustomMessage(): bool
    {
        return $this->content_type === self::CONTENT_CUSTOM;
    }

    public function hasStaticReport(): bool
    {
        return $this->report_mode === self::REPORT_MODE_STATIC;
    }

    public function hasLiveReport(): bool
    {
        return $this->report_mode === self::REPORT_MODE_LIVE;
    }

    public function getLatestDelivery(): ?DistributionDelivery
    {
        return $this->deliveries()->latest()->first();
    }

    public static function getDistributionTypes(): array
    {
        return [
            self::TYPE_ONE_TIME => 'One-time',
            self::TYPE_RECURRING => 'Recurring',
        ];
    }

    public static function getChannels(): array
    {
        return [
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getContentTypes(): array
    {
        return [
            self::CONTENT_REPORT => 'Link Report',
            self::CONTENT_CUSTOM => 'Custom Message',
        ];
    }

    public static function getReportModes(): array
    {
        return [
            self::REPORT_MODE_STATIC => 'Static (PDF snapshot)',
            self::REPORT_MODE_LIVE => 'Live (dynamic link)',
        ];
    }

    public static function getRecipientTypes(): array
    {
        return [
            self::RECIPIENT_CONTACT_LIST => 'Contact List',
            self::RECIPIENT_INDIVIDUAL => 'Individual Contacts',
            self::RECIPIENT_QUERY => 'Dynamic Query',
        ];
    }
}
