<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the king dashboard.
     */
    public function accessKingDashboard(User $user): bool
    {
        return $user->isKing(); // Uses the existing method on User model
    }

    // Add other User policy methods here if needed in the future
}
