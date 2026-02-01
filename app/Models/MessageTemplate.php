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
        return [
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
        ];
    }

    public static function getMergeFields(): array
    {
        return [
            'recipient' => [
                '{{first_name}}' => 'Recipient first name',
                '{{last_name}}' => 'Recipient last name',
                '{{full_name}}' => 'Recipient full name',
                '{{email}}' => 'Recipient email',
                '{{phone}}' => 'Recipient phone',
            ],
            'organization' => [
                '{{organization_name}}' => 'Organization name',
            ],
            'report' => [
                '{{report_link}}' => 'Link to report (for live reports)',
            ],
            'system' => [
                '{{unsubscribe_link}}' => 'Unsubscribe link',
                '{{sender_name}}' => 'Sender name',
                '{{sender_email}}' => 'Sender email',
            ],
            'student' => [
                '{{student_name}}' => 'Student full name',
                '{{grade_level}}' => 'Student grade level',
                '{{classroom}}' => 'Student classroom',
                '{{teacher_name}}' => 'Teacher name',
            ],
        ];
    }
}
