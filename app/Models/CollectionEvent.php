<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title',
        'schema_mapping',
        'is_anonymous',
    ];

    protected $casts = [
        'schema_mapping' => 'array',
        'is_anonymous' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(CollectionToken::class);
    }

    public function pendingExtractions(): HasMany
    {
        return $this->hasMany(PendingExtraction::class);
    }
}
