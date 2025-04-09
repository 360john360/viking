<?php

namespace App\Policies;

use App\Models\KingdomJoinRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // HandlesAuthorization trait is often included

class KingdomJoinRequestPolicy
{
    // Use HandlesAuthorization; // Trait can be used for convenience, or implement manually

    /**
     * Determine whether the user can approve the model.
     * Only the King of the target kingdom can approve.
     */
    public function approve(User $user, KingdomJoinRequest $joinRequest): bool
    {
        // Eager load kingdom relationship if not already loaded for efficiency
        $joinRequest->loadMissing('kingdom');

        // Check if the kingdom exists and if the user is the king of that kingdom
        return $joinRequest->kingdom && $joinRequest->kingdom->king_user_id === $user->id;
    }

    /**
     * Determine whether the user can reject the model.
     * Only the King of the target kingdom can reject.
     */
    public function reject(User $user, KingdomJoinRequest $joinRequest): bool
    {
         // Eager load kingdom relationship if not already loaded
         $joinRequest->loadMissing('kingdom');

         // Same logic as approve for now
         return $joinRequest->kingdom && $joinRequest->kingdom->king_user_id === $user->id;
    }


    // --- Standard Policy Methods (Defaulting to No for now) ---

    /**
     * Determine whether the user can view any models.
     * (e.g., maybe site admins later?)
     */
    public function viewAny(User $user): bool
    {
        return false; // Default deny
    }

    /**
     * Determine whether the user can view the model.
     * (e.g., maybe the applicant and the king?)
     */
    public function view(User $user, KingdomJoinRequest $kingdomJoinRequest): bool
    {
         return false; // Default deny - implement later
    }

    /**
     * Determine whether the user can create models.
     * (Users create these via the requestToJoin action, handled separately)
     */
    public function create(User $user): bool
    {
        return false; // Users don't directly "create" approved requests
    }

    /**
     * Determine whether the user can update the model.
     * (Approving/Rejecting is handled by specific methods)
     */
    public function update(User $user, KingdomJoinRequest $kingdomJoinRequest): bool
    {
        return false; // Use approve/reject methods instead
    }

    /**
     * Determine whether the user can delete the model.
     * (Maybe admins or the user who requested it before approval?)
     */
    public function delete(User $user, KingdomJoinRequest $kingdomJoinRequest): bool
    {
        return false; // Default deny - implement later if needed
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KingdomJoinRequest $kingdomJoinRequest): bool
    {
         return false; // Not using soft deletes on this model
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KingdomJoinRequest $kingdomJoinRequest): bool
    {
         return false; // Not using soft deletes on this model
    }
}