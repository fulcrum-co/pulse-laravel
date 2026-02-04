<?php

namespace App\Services;

use App\Models\CollectionEvent;
use App\Models\CollectionToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CollectionTokenService
{
    public function createSignedLink(CollectionEvent $event, Model $contact): string
    {
        $token = CollectionToken::create([
            'token' => Str::random(64),
            'collection_event_id' => $event->id,
            'contact_id' => $contact->id,
            'expires_at' => now()->addHours(48),
        ]);

        return URL::temporarySignedRoute('collect.token', $token->expires_at, [
            'token' => $token->token,
        ]);
    }
}
