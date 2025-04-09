<?php

namespace App\Http\Controllers;

use App\Models\Tribe;
use App\Models\TribeJoinRequest;
use App\Models\KingdomMembership; // Needed for Leave Kingdom
use App\Models\SiteSetting;     // Needed for Leave Kingdom Cooldown
use App\Models\UserCooldown;     // Needed for Leave Kingdom Cooldown
use App\Models\User;             // Needed for Leave Kingdom
use App\Policies\TribePolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TribeMembershipController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle a request from a user to join a specific tribe.
     */
    public function requestToJoin(Request $request, Tribe $tribe): RedirectResponse
    {
        $user = $request->user();
        try {
            $this->authorize('view', $tribe); // Check if user can view (implies correct kingdom etc)
        } catch (Exception $e) {
             Log::warning("Unauthorized Tribe Join Attempt: User {$user->id} to Tribe {$tribe->id}. Reason: Cannot view tribe.");
             return redirect()->back()->with('error', 'You cannot request to join this tribe.');
        }

        if ($user->current_tribe_id !== null) {
            Log::warning("Tribe Join Request Failed: User {$user->id} already in Tribe {$user->current_tribe_id}. Attempted Tribe {$tribe->id}.");
            return redirect()->back()->with('error', 'You are already in a tribe. Leave your current tribe first.');
        }
        $existingRequest = TribeJoinRequest::where('user_id', $user->id)
                                             ->where('tribe_id', $tribe->id)
                                             ->where('status', 'pending')
                                             ->exists();
        if ($existingRequest) {
             return redirect()->back()->with('info', 'Your request to join this tribe is already pending.');
        }

        try {
            TribeJoinRequest::create([
                'user_id' => $user->id,
                'tribe_id' => $tribe->id,
            ]);
            Log::info("Tribe Join Request Submitted: User {$user->id} requested to join Tribe {$tribe->id}.");
            return redirect()->back()->with('status', 'Tribe join request submitted successfully!');
        } catch (Exception $e) {
             Log::error("Error creating tribe join request for User {$user->id} / Tribe {$tribe->id}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->back()->with('error', 'Could not submit join request due to a server error.');
        }
    }

    /**
     * Handle a request for a user to leave their current tribe.
     * (Placeholder - logic to be added later)
     */
    public function leaveTribe(Request $request): RedirectResponse
    {
        $user = $request->user();

        // TODO: Add authorization (can user leave?)
        // TODO: Check if user is actually in a tribe
        // TODO: Check if user is the leader (implement leave constraint)
        // TODO: DB Transaction: Delete TribeMembership, Update User->current_tribe_id = null
        // TODO: Redirect with message

        Log::info("Placeholder: User {$user->id} attempted to leave tribe.");
        return redirect()->route('dashboard')->with('info', 'Leave Tribe Functionality not yet implemented.');
    }

    /**
      * Handle a request for a user to leave their current KINGDOM.
      * (This method correctly belongs here or in a dedicated KingdomMembershipController)
      */
     public function leaveKingdom(Request $request): RedirectResponse
     {
         $user = $request->user();
         $membership = KingdomMembership::where('user_id', $user->id)->with('kingdom')->first();

         if (!$membership || !$membership->kingdom) {
              Log::warning("Leave Kingdom Consistency Issue: User {$user->id} has current_kingdom_id {$user->current_kingdom_id} but no valid membership record found.");
              if ($user->current_kingdom_id !== null || $user->current_tribe_id !== null) {
                   $user->update(['current_kingdom_id' => null, 'current_tribe_id' => null]);
              }
              return redirect()->route('dashboard')->with('error', 'Could not find your kingdom membership record.');
         }
         if ($membership->kingdom->king_user_id === $user->id) {
             Log::warning("Leave Kingdom Prevented: User {$user->id} is King of Kingdom {$membership->kingdom_id}.");
             return redirect()->route('dashboard')->with('error', 'As King, you cannot leave the kingdom directly. Please use abdication or contact support.');
         }

         $leavingKingdomId = $membership->kingdom_id;
         $cooldownSetting = SiteSetting::where('key', 'default_kingdom_cooldown_days')->first();
         $cooldownDays = $cooldownSetting ? (int)$cooldownSetting->value : 7;

         try {
             DB::transaction(function () use ($user, $membership, $cooldownDays, $leavingKingdomId) {
                 $membership->delete();
                 $user->update([ // Use update here
                     'current_kingdom_id' => null,
                     'current_tribe_id' => null,
                 ]);
                 UserCooldown::create([
                     'user_id' => $user->id,
                     'cooldown_type' => 'kingdom_join',
                     'expires_at' => Carbon::now()->addDays($cooldownDays),
                     'reason_kingdom_id' => $leavingKingdomId,
                 ]);
                 Log::info("User Left Kingdom: User {$user->id} left Kingdom {$leavingKingdomId}.");
             });
             return redirect()->route('dashboard')->with('status', 'You have successfully left your kingdom.');
         } catch (Exception $e) {
              Log::error("Error during leave kingdom process for User {$user->id}: " . $e->getMessage(), ['exception' => $e]);
              return redirect()->route('dashboard')->with('error', 'Could not leave kingdom due to a server error.');
         }
     }

}