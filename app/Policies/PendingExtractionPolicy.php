<?php

namespace App\Policies;

use App\Models\PendingExtraction;
use App\Models\User;

class PendingExtractionPolicy
{
    public function view(User $user, PendingExtraction $extraction): bool
    {
        return $user->isAdmin() && $user->org_id === $extraction->contact->org_id;
    }

    public function update(User $user, PendingExtraction $extraction): bool
    {
        return $this->view($user, $extraction);
    }
}
