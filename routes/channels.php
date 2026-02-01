<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Private user notification channel.
 * Only the authenticated user can listen to their own notification channel.
 */
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Organization-wide notification channel.
 * Users can only listen to their organization's channel.
 */
Broadcast::channel('organization.{orgId}', function ($user, $orgId) {
    return (int) $user->organization_id === (int) $orgId;
});
