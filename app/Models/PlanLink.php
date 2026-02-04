<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'linkable_type',
        'linkable_id',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
