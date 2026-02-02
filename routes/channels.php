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

/**
 * Report collaboration presence channel.
 * Returns user data for presence tracking when they join.
 */
Broadcast::channel('report.{reportId}', function ($user, $reportId) {
    // Check if user has access to this report
    $report = \App\Models\CustomReport::find($reportId);

    if (! $report) {
        return false;
    }

    // Check if user is the creator
    if ($report->created_by === $user->id) {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?? $user->name,
            'avatar' => $user->profile_photo_url ?? null,
            'role' => 'owner',
        ];
    }

    // Check if user is a collaborator
    $collaborator = $report->collaborators()->where('user_id', $user->id)->first();
    if ($collaborator) {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?? $user->name,
            'avatar' => $user->profile_photo_url ?? null,
            'role' => $collaborator->role,
        ];
    }

    // Check if user is in the same organization
    if ($report->org_id && $user->org_id === $report->org_id) {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?? $user->name,
            'avatar' => $user->profile_photo_url ?? null,
            'role' => 'viewer',
        ];
    }

    return false;
});
