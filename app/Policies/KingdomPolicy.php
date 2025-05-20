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

    // --- New RBAC Policy Methods ---

    public function manageMembers(User $user, Kingdom $kingdom): bool
    {
        // King of this kingdom or a Kingdom Moderator of this kingdom
        return ($user->id === $kingdom->king_user_id) || $user->isKingdomModerator($kingdom);
    }

    public function manageModerators(User $user, Kingdom $kingdom): bool
    {
        // Only the King of this kingdom
        return $user->id === $kingdom->king_user_id;
    }

    public function setPolicy(User $user, Kingdom $kingdom): bool
    {
        // Only the King of this kingdom
        return $user->id === $kingdom->king_user_id;
    }

    public function setTax(User $user, Kingdom $kingdom): bool
    {
        // Only the King of this kingdom
        return $user->id === $kingdom->king_user_id;
    }

    public function manageTreasury(User $user, Kingdom $kingdom): bool
    {
        // Only the King of this kingdom
        return $user->id === $kingdom->king_user_id;
    }

    public function declareInternalWar(User $user, Kingdom $kingdom): bool // Assuming 'kingdom.declare_war' means internal
    {
        // Only the King of this kingdom
        return $user->id === $kingdom->king_user_id;
    }

    /**
     * Determine whether the user can create a king claim for the kingdom.
     */
    public function createClaim(User $user, Kingdom $kingdom): bool
    {
        // Already checked for auth in controller, this is for the general ability
        if (!$user->is_king_candidate_verified) {
            return false;
        }
        if (!$kingdom->is_active || $kingdom->king_user_id !== null) {
            return false; // Kingdom not active or already has a king
        }
        // Check for existing pending claim by this user for THIS kingdom
        // Note: KingClaim model needs to be imported or fully qualified if not. Assuming it's available.
        // For this policy, we also need to ensure the user doesn't have *any* other pending claim.
        if (\App\Models\KingClaim::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            // This covers both a claim for this kingdom or any other kingdom.
            return false; 
        }
        // Check for 'kingdom_join' cooldown
        // Note: UserCooldown model needs to be imported or fully qualified if not. Assuming it's available.
        if ($user->cooldowns()->where('cooldown_type', 'kingdom_join')->where('expires_at', '>', now())->exists()) {
            return false;
        }
        // Optional: Check if user is already a king of another kingdom
        if ($user->isKing()) { // isKing() helper assumed to be on User model
            return false;
        }

        return true;
    }
}