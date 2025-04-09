<?php

namespace App\Http\Controllers;

use App\Models\KingdomMembership;
use App\Models\SiteSetting;
use App\Models\UserCooldown;
use App\Models\Kingdom;
use App\Models\User; // Added for clarity
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KingdomMembershipController extends Controller
{
    /**
     * Handle a request for a user to leave their current kingdom.
     */
    public function leave(Request $request): RedirectResponse
    {
        $user = $request->user(); // Get authenticated user

        // 1. Verify user has a membership record.
        $membership = KingdomMembership::where('user_id', $user->id)->first();

        // If no membership record exists AT ALL, something is inconsistent.
        if (!$membership) {
            Log::warning("Leave Kingdom Consistency Issue: User {$user->id} has current_kingdom_id {$user->current_kingdom_id} but no membership record found.");
            // Ensure user record is also nullified if inconsistent
            if ($user->current_kingdom_id !== null || $user->current_tribe_id !== null) {
                 $user->update(['current_kingdom_id' => null, 'current_tribe_id' => null]);
            }
            return redirect()->route('dashboard')->with('error', 'Could not find your kingdom membership record.');
        }

        // Now we know membership exists, get the related kingdom ID
        $leavingKingdomId = $membership->kingdom_id;
        // Try to load the actual kingdom model (even if soft-deleted for the King check)
        $kingdom = Kingdom::withTrashed()->find($leavingKingdomId); // Use withTrashed()

        // 2. Check if user IS the King of the associated kingdom.
        // This check MUST happen before attempting the transaction.
        // Check against the loaded kingdom model (which might be soft-deleted).
        if ($kingdom && $kingdom->king_user_id === $user->id) {
            Log::warning("Leave Kingdom Prevented: User {$user->id} is King of Kingdom {$leavingKingdomId} and cannot leave via this method.");
            return redirect()->route('dashboard')->with('error', 'As King, you cannot leave the kingdom directly. Please use abdication or contact support.');
        }

        // If they are not the king, proceed with leaving...

        // 3. Get cooldown duration
        $cooldownSetting = SiteSetting::where('key', 'default_kingdom_cooldown_days')->first();
        $cooldownDays = $cooldownSetting ? (int)$cooldownSetting->value : 7;

        // 4. Perform actions within a database transaction
        try {
            DB::transaction(function () use ($user, $membership, $cooldownDays, $leavingKingdomId) {
                // Step A: Delete the membership record
                $membership->delete();

                // Step B: Update the user's status (ensure it happens)
                $user->current_kingdom_id = null; // Direct assignment before save
                $user->current_tribe_id = null;
                $user->save(); // Explicit save

                // Step C: Create the cooldown record
                UserCooldown::create([
                    'user_id' => $user->id,
                    'cooldown_type' => 'kingdom_join',
                    'expires_at' => Carbon::now()->addDays($cooldownDays),
                    'reason_kingdom_id' => $leavingKingdomId,
                ]);

                Log::info("User Left Kingdom: User {$user->id} left Kingdom {$leavingKingdomId}. Cooldown applied for {$cooldownDays} days.");
            });

            // 5. Redirect with success
            return redirect()->route('dashboard')->with('status', 'You have successfully left your kingdom.');

        } catch (\Exception $e) {
             Log::error("Error during leave kingdom process for User {$user->id}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->route('dashboard')->with('error', 'Could not leave kingdom due to a server error. Please try again.');
        }
    }
}