<?php

namespace App\Http\Controllers;

use App\Models\CollectionToken;
use Illuminate\Http\Request;

class EphemeralCollectionController extends Controller
{
    public function show(Request $request, string $token)
    {
        $record = CollectionToken::with(['event.organization.settings', 'contact'])
            ->where('token', $token)
            ->first();

        if (! $record || $record->isExpired() || $record->isUsed()) {
            return response()->view('collect.link-expired', [], 410);
        }

        return view('collect.dictate', [
            'token' => $record,
        ]);
    }
}
