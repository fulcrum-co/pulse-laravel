<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'org_id',
        'wallet_id',
        'type',
        'amount',
        'balance_after',
        'action_type',
        'description',
        'metadata',
        'user_id',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Transaction types.
     */
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_USAGE = 'usage';

    public const TYPE_REFUND = 'refund';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_BONUS = 'bonus';

    /**
     * Action types (what consumed the credits).
     */
    public const ACTION_AI_ANALYSIS = 'ai_analysis';

    public const ACTION_AI_SUMMARY = 'ai_summary';

    public const ACTION_TRANSCRIPTION = 'transcription_minute';

    public const ACTION_SMS_OUTBOUND = 'sms_outbound';

    public const ACTION_SMS_INBOUND = 'sms_inbound';

    public const ACTION_WHATSAPP_OUTBOUND = 'whatsapp_outbound';

    public const ACTION_WHATSAPP_INBOUND = 'whatsapp_inbound';

    public const ACTION_FEATURE_VALVE_CHANGE = 'feature_valve_change';

    /**
     * Get the organization this transaction belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the wallet this transaction belongs to.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CreditWallet::class, 'wallet_id');
    }

    /**
     * Get the user who made this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic).
     */
    public function reference()
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        return $this->reference_type::find($this->reference_id);
    }

    /**
     * Check if this is a credit (positive amount).
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if this is a debit (negative amount).
     */
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get formatted amount with sign.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';

        return $prefix.number_format($this->amount, 0).' credits';
    }

    /**
     * Get type badge color.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PURCHASE => 'green',
            self::TYPE_BONUS => 'blue',
            self::TYPE_USAGE => 'gray',
            self::TYPE_REFUND => 'yellow',
            self::TYPE_ADJUSTMENT => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get human-readable action type.
     */
    public function getActionDisplayAttribute(): string
    {
        return match ($this->action_type) {
            self::ACTION_AI_ANALYSIS => 'AI Analysis',
            self::ACTION_AI_SUMMARY => 'AI Summary',
            self::ACTION_TRANSCRIPTION => 'Transcription',
            self::ACTION_SMS_OUTBOUND => 'SMS Sent',
            self::ACTION_SMS_INBOUND => 'SMS Received',
            self::ACTION_WHATSAPP_OUTBOUND => 'WhatsApp Sent',
            self::ACTION_WHATSAPP_INBOUND => 'WhatsApp Received',
            self::ACTION_FEATURE_VALVE_CHANGE => 'Feature Toggle',
            default => $this->action_type ?? 'N/A',
        };
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter purchases only.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', self::TYPE_PURCHASE);
    }

    /**
     * Scope to filter usage only.
     */
    public function scopeUsage($query)
    {
        return $query->where('type', self::TYPE_USAGE);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeForAction($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
