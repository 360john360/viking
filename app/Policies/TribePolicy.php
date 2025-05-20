<?php

namespace App\Policies;

use App\Models\Tribe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TribePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, Tribe $tribe): bool
    {
        if (!$user) { return false; }
        $tribe->loadMissing('kingdom:id,is_active');
        if (!$tribe->is_active || !$tribe->kingdom || !$tribe->kingdom->is_active) { return false; }
        if ($user->isSiteAdmin()) { return true; }
        return $user->current_kingdom_id === $tribe->kingdom_id;
    }

    /**
     * Determine whether the user can view join requests for the tribe.
     */
    public function viewAnyRequests(User $user, Tribe $tribe): bool // <-- DEBUG REMOVED
    {
        // Allow Site Admin OR Officer/Leader of this specific Tribe
        return $user->isSiteAdmin() || $user->isTribeOfficerOrLeader($tribe); // <-- Restored return
    }

    public function create(User $user): bool
    {
        // Site Admin OR any King can generally create tribes (controller handles context)
        return $user->isSiteAdmin() || $user->isKing();
    }

    public function update(User $user, Tribe $tribe): bool
    {
        $tribe->loadMissing('kingdom:id,king_user_id'); // Ensure kingdom data is loaded
        return $user->isSiteAdmin() || 
               ($tribe->kingdom && $user->id === $tribe->kingdom->king_user_id) || 
               ($user->id === $tribe->leader_user_id);
    }

    public function delete(User $user, Tribe $tribe): bool
    {
        return false; // Deactivation via update
    }

    public function restore(User $user, Tribe $tribe): bool
    {
         return $user->isSiteAdmin();
    }

    public function forceDelete(User $user, Tribe $tribe): bool
    {
        return false;
    }

    public function manageMembers(User $user, Tribe $tribe): bool
    {
        // Site Admin, King of parent Kingdom, Tribe Leader, or Tribe Officer of this tribe
        $tribe->loadMissing('kingdom:id,king_user_id');
        return $user->isSiteAdmin() ||
               ($tribe->kingdom && $user->id === $tribe->kingdom->king_user_id) ||
               $user->id === $tribe->leader_user_id || 
               $user->isTribeOfficer($tribe);
    }

    public function manageOfficers(User $user, Tribe $tribe): bool
    {
        // Site Admin, King of parent Kingdom, or Tribe Leader of this tribe
        $tribe->loadMissing('kingdom:id,king_user_id');
        return $user->isSiteAdmin() ||
               ($tribe->kingdom && $user->id === $tribe->kingdom->king_user_id) ||
               ($user->id === $tribe->leader_user_id);
    }
}