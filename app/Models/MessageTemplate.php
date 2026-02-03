<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageTemplate extends Model
{
    use HasFactory;

    const CHANNEL_EMAIL = 'email';

    const CHANNEL_SMS = 'sms';

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'channel',
        'subject',
        'body',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function scopeEmail($query)
    {
        return $query->where('channel', self::CHANNEL_EMAIL);
    }

    public function scopeSms($query)
    {
        return $query->where('channel', self::CHANNEL_SMS);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public static function getChannels(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            self::CHANNEL_EMAIL => $terminology->get('email_label'),
            self::CHANNEL_SMS => $terminology->get('sms_label'),
        ];
    }

    public static function getMergeFields(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            'recipient' => [
                '{{first_name}}' => $terminology->get('recipient_first_name_label'),
                '{{last_name}}' => $terminology->get('recipient_last_name_label'),
                '{{full_name}}' => $terminology->get('recipient_full_name_label'),
                '{{email}}' => $terminology->get('recipient_email_label'),
                '{{phone}}' => $terminology->get('recipient_phone_label'),
            ],
            'organization' => [
                '{{organization_name}}' => $terminology->get('organization_name_label'),
            ],
            'report' => [
                '{{report_link}}' => $terminology->get('report_link_label'),
            ],
            'system' => [
                '{{unsubscribe_link}}' => $terminology->get('unsubscribe_link_label'),
                '{{sender_name}}' => $terminology->get('sender_name_label'),
                '{{sender_email}}' => $terminology->get('sender_email_label'),
            ],
            'participant' => [
                '{{learner_name}}' => $terminology->get('participant_full_name_label'),
                '{{level}}' => $terminology->get('participant_level_label'),
                '{{learning_group}}' => $terminology->get('participant_learning_group_label'),
                '{{instructor_name}}' => $terminology->get('instructor_name_label'),
            ],
        ];
    }
}
