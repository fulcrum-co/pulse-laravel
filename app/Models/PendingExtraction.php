<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendingExtraction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'collection_event_id',
        'raw_transcript',
        'audio_path',
        'extracted_data',
        'confidence_score',
        'status',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'confidence_score' => 'integer',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function collectionEvent(): BelongsTo
    {
        return $this->belongsTo(CollectionEvent::class, 'collection_event_id');
    }
}
