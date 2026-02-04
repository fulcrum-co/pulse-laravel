<?php

namespace App\Events;

use App\Models\ContactNote;
use App\Models\StrategyDriftScore;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StrategyDriftDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ContactNote $note,
        public StrategyDriftScore $score
    ) {}
}
