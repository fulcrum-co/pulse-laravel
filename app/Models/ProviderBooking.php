<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ProviderBooking extends Model
{
    protected $fillable = [
        'uuid',
        'provider_id',
        'conversation_id',
        'booked_by_type',
        'booked_by_id',
        'student_id',
        'booking_type',
        'status',
        'scheduled_at',
        'duration_minutes',
        'location_type',
        'location_details',
        'notes',
        'provider_notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by_type',
        'cancelled_by_id',
        'confirmed_at',
        'completed_at',
        'reminder_sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Booking types
    const TYPE_CONSULTATION = 'consultation';
    const TYPE_SESSION = 'session';
    const TYPE_ASSESSMENT = 'assessment';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    // Location types
    const LOCATION_IN_PERSON = 'in_person';
    const LOCATION_REMOTE = 'remote';
    const LOCATION_PHONE = 'phone';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the provider.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the conversation this booking came from.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ProviderConversation::class, 'conversation_id');
    }

    /**
     * Get who booked (User or Student).
     */
    public function bookedBy(): MorphTo
    {
        return $this->morphTo('booked_by');
    }

    /**
     * Get the student receiving the service.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get who cancelled (if cancelled).
     */
    public function cancelledBy(): MorphTo
    {
        return $this->morphTo('cancelled_by');
    }

    /**
     * Get the payment for this booking.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(ProviderPayment::class, 'booking_id');
    }

    /**
     * Check if booking is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if booking is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            && $this->scheduled_at->isFuture();
    }

    /**
     * Confirm the booking.
     */
    public function confirm(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Cancel the booking.
     */
    public function cancel($cancelledBy, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_type' => get_class($cancelledBy),
            'cancelled_by_id' => $cancelledBy->id,
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(): void
    {
        $this->update(['status' => self::STATUS_NO_SHOW]);
    }

    /**
     * Get the end time of the booking.
     */
    public function getEndTimeAttribute(): \Carbon\Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_minutes < 60) {
            return "{$this->duration_minutes} min";
        }

        $hours = floor($this->duration_minutes / 60);
        $mins = $this->duration_minutes % 60;

        if ($mins === 0) {
            return "{$hours} hr";
        }

        return "{$hours} hr {$mins} min";
    }

    /**
     * Get location type label.
     */
    public function getLocationTypeLabelAttribute(): string
    {
        return match ($this->location_type) {
            self::LOCATION_IN_PERSON => 'In-Person',
            self::LOCATION_REMOTE => 'Video Call',
            self::LOCATION_PHONE => 'Phone Call',
            default => $this->location_type,
        };
    }

    /**
     * Scope: upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->orderBy('scheduled_at');
    }

    /**
     * Scope: past bookings.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->orWhereIn('status', [self::STATUS_COMPLETED, self::STATUS_NO_SHOW, self::STATUS_CANCELLED])
            ->orderByDesc('scheduled_at');
    }

    /**
     * Scope: for a specific provider.
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope: for a specific student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: needing reminder (24 hours before).
     */
    public function scopeNeedingReminder($query)
    {
        return $query->whereNull('reminder_sent_at')
            ->where('status', self::STATUS_CONFIRMED)
            ->whereBetween('scheduled_at', [
                now()->addHours(23),
                now()->addHours(25),
            ]);
    }
}
