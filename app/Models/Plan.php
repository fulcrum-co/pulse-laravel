<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'status',
        'review_at',
    ];

    protected $casts = [
        'review_at' => 'datetime',
    ];

    public function plannable(): MorphTo
    {
        return $this->morphTo();
    }

    public function links(): HasMany
    {
        return $this->hasMany(PlanLink::class);
    }
}
