<?php

namespace App\Http\Controllers;

use App\Models\Tribe;
use App\Models\TribeJoinRequest;
use App\Models\KingdomMembership; // Needed for Leave Kingdom
use App\Models\SiteSetting;     // Needed for Leave Kingdom Cooldown
use App\Models\UserCooldown;     // Needed for Leave Kingdom Cooldown
use App\Models\User;
use App\Models\TribeMembership; // Added for leaveTribe
use App\Policies\TribePolicy;
use App\Events\UserLeftTribe; // Added for leaveTribe
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

        // Check if the user is a member of the tribe's parent kingdom
        if (Auth::user()->current_kingdom_id !== $tribe->kingdom_id) {
            Log::warning("Tribe Join Request Failed: User {Auth::user()->id} attempting to join Tribe {$tribe->id} is not in the tribe's parent Kingdom {$tribe->kingdom_id}. User's kingdom: " . (Auth::user()->current_kingdom_id ?? 'None'));
            return redirect()->back()->with('error', 'You must be a member of this tribe\'s parent kingdom (' . $tribe->kingdom->name . ') to request membership.');
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

        // Check for active tribe_join cooldown
        $activeTribeCooldown = UserCooldown::where('user_id', $user->id)
                                           ->where('cooldown_type', 'tribe_join')
                                           ->where('expires_at', '>', Carbon::now())
                                           ->exists();
        if ($activeTribeCooldown) {
            Log::warning("Tribe Join Request Failed: User {$user->id} has an active 'tribe_join' cooldown. Attempted Tribe {$tribe->id}.");
            return redirect()->back()->with('error', 'You have an active cooldown preventing you from joining tribes at this time.');
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

        if (!$user->current_tribe_id) {
            Log::warning("Leave Tribe Attempt: User {$user->id} attempted to leave tribe but is not currently in one.");
            return redirect()->route('dashboard')->with('error', 'You are not currently a member of any tribe.');
        }

        $membership = TribeMembership::where('user_id', $user->id)
                                     ->where('tribe_id', $user->current_tribe_id)
                                     ->first();

        if (!$membership) {
            Log::error("Leave Tribe Consistency Issue: User {$user->id} has current_tribe_id {$user->current_tribe_id} but no TribeMembership record found.");
            // Defensive update to user model if inconsistent
            $user->current_tribe_id = null;
            $user->save();
            return redirect()->route('dashboard')->with('error', 'Could not find your tribe membership record. Your status has been updated.');
        }

        $tribe = Tribe::find($membership->tribe_id);
        if (!$tribe) {
            // This case should be rare if foreign keys are set up, but good to handle.
            Log::error("Leave Tribe Critical Issue: TribeMembership record exists for Tribe ID {$membership->tribe_id} but Tribe not found. User {$user->id}.");
            $user->current_tribe_id = null;
            $user->save();
            $membership->delete(); // Clean up orphaned membership
            return redirect()->route('dashboard')->with('error', 'The tribe you were part of seems to no longer exist. Your status has been updated.');
        }

        $leavingTribeId = $tribe->id;
        $cooldownDays = 3; // Default to 3 days for tribe leave cooldown

        try {
            DB::transaction(function () use ($user, $membership, $tribe, $cooldownDays, $leavingTribeId) {
                // If user is the leader, set tribe's leader_user_id to null
                if ($user->id === $tribe->leader_user_id) {
                    $tribe->leader_user_id = null;
                    $tribe->save();
                    Log::info("Tribe Leader Stepped Down: User {$user->id} was leader of Tribe {$tribe->id}. Tribe leader_user_id set to null.");
                }

                // Delete the membership record
                $membership->delete();

                // Update the user's status
                $user->current_tribe_id = null;
                $user->save();

                // Create the cooldown record
                UserCooldown::create([
                    'user_id' => $user->id,
                    'cooldown_type' => 'tribe_join',
                    'expires_at' => Carbon::now()->addDays($cooldownDays),
                    // 'reason_tribe_id' => $leavingTribeId, // This line is now removed
                ]);

                // Dispatch the event
                UserLeftTribe::dispatch($user, $leavingTribeId);

                Log::info("User Left Tribe: User {$user->id} left Tribe {$leavingTribeId}. Cooldown for {$cooldownDays} days applied. UserLeftTribe event dispatched.");
            });

            return redirect()->route('dashboard')->with('status', 'You have successfully left your tribe.');

        } catch (Exception $e) {
            Log::error("Error during leave tribe process for User {$user->id} and Tribe {$leavingTribeId}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Could not leave tribe due to a server error. Please try again.');
        }
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