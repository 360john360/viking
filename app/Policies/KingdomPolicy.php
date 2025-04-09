<?php

namespace App\Policies;

use App\Models\Kingdom;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KingdomPolicy
{
    // use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can see the list page
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kingdom $kingdom): bool
    {
        // Allow viewing only if the kingdom is active
        // (Could add checks later for members/kings viewing inactive ones)
        return $kingdom->is_active;
    }

    /**
     * Determine whether the user can create models.
     * Only Site Admins can create kingdoms.
     */
    public function create(User $user): bool
    {
        // Use the helper method defined in the User model
        return $user->isSiteAdmin(); // <-- MODIFIED
    }

    /**
     * Determine whether the user can update the model.
     * Only Site Admins can update kingdom details for now.
     * (Could be expanded to allow the King later: || $user->id === $kingdom->king_user_id)
     */
    public function update(User $user, Kingdom $kingdom): bool
    {
        // Use the helper method defined in the User model
        return $user->isSiteAdmin(); // <-- MODIFIED
    }

    /**
     * Determine whether the user can delete the model.
     * Only Site Admins can delete kingdoms.
     */
    public function delete(User $user, Kingdom $kingdom): bool
    {
         // Use the helper method defined in the User model
        return $user->isSiteAdmin(); // <-- MODIFIED
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Kingdom $kingdom): bool
    {
         // Only Site Admins can restore soft-deleted kingdoms
         return $user->isSiteAdmin(); // <-- MODIFIED (Assuming only admins)
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Kingdom $kingdom): bool
    {
         // Only Site Admins can force delete kingdoms
         return $user->isSiteAdmin(); // <-- MODIFIED (Assuming only admins)
    }
}