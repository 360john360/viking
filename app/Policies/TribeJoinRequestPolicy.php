<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TribeJoinRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class TribeJoinRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can approve the tribe join request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TribeJoinRequest  $tribeJoinRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(User $user, TribeJoinRequest $tribeJoinRequest): bool
    {
        $tribeJoinRequest->loadMissing('tribe');
        if (!$tribeJoinRequest->tribe) {
            return false;
        }
        // User is leader of the tribe OR an officer of the tribe (using existing User model helper)
        return $user->id === $tribeJoinRequest->tribe->leader_user_id || $user->isTribeOfficer($tribeJoinRequest->tribe);
    }

    /**
     * Determine whether the user can reject the tribe join request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TribeJoinRequest  $tribeJoinRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reject(User $user, TribeJoinRequest $tribeJoinRequest): bool
    {
        $tribeJoinRequest->loadMissing('tribe');
        if (!$tribeJoinRequest->tribe) {
            return false;
        }
        return $user->id === $tribeJoinRequest->tribe->leader_user_id || $user->isTribeOfficer($tribeJoinRequest->tribe);
    }
}
