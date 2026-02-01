<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Unsubscribe a user from digest emails.
     * This uses a signed URL to verify the request.
     */
    public function unsubscribe(Request $request, User $user)
    {
        // Signed URL middleware validates the signature
        // Update user's digest preferences
        $prefs = $user->notification_preferences;
        $prefs['digest']['enabled'] = false;
        $user->update(['notification_preferences' => $prefs]);

        return view('notifications.unsubscribed', [
            'user' => $user,
        ]);
    }
}
