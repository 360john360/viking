<?php

namespace App\Http\Controllers;

use App\Events\KingClaimSubmitted;
use App\Models\Kingdom;
use App\Models\KingClaim;
use App\Models\UserCooldown; // Import UserCooldown
use Carbon\Carbon; // Import Carbon
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KingClaimController extends Controller
{
    /**
     * Show the form for creating a new king claim.
     *
     * @param  \App\Models\Kingdom  $kingdom
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Kingdom $kingdom)
    {
        $user = Auth::user();

        // Check for active kingdom_join cooldown
        $activeKingdomCooldown = \App\Models\UserCooldown::where('user_id', $user->id)
                                            ->where('cooldown_type', 'kingdom_join')
                                            ->where('expires_at', '>', \Carbon\Carbon::now()) 
                                            ->exists();
        if ($activeKingdomCooldown) {
            Log::warning("King Claim Attempt Failed: User {$user->id} has an active 'kingdom_join' cooldown.");
            return redirect()->route('dashboard')->with('error', 'You have an active cooldown from recently leaving a kingdom, and cannot claim a new one yet.');
        }

        // Authorization checks
        if (!$user->is_king_candidate_verified) {
            Log::warning("KingClaim Create: User {$user->id} is not king candidate verified. Kingdom: {$kingdom->id}");
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'You are not verified as a king candidate.');
        }

        if ($user->isKing()) { // Assumes isKing() method checks if user is king of ANY kingdom
            Log::warning("KingClaim Create: User {$user->id} is already a king. Kingdom: {$kingdom->id}");
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'You are already a King.');
        }

        if (!$kingdom->is_active) {
            Log::warning("KingClaim Create: Kingdom {$kingdom->id} is not active. User: {$user->id}");
            return redirect()->route('kingdoms.index')->with('error', 'This kingdom is not currently active.');
        }

        if ($kingdom->king_user_id !== null) {
            Log::warning("KingClaim Create: Kingdom {$kingdom->id} already has a king (King ID: {$kingdom->king_user_id}). User: {$user->id}");
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'This kingdom already has a ruler.');
        }

        $existingClaim = KingClaim::where('user_id', $user->id)
                                  ->where('kingdom_id', $kingdom->id)
                                  ->where('status', 'pending')
                                  ->exists();
        if ($existingClaim) {
            Log::info("KingClaim Create: User {$user->id} already has a pending claim for Kingdom {$kingdom->id}.");
            return redirect()->route('kingdoms.show', $kingdom)->with('info', 'You already have a pending claim for this kingdom.');
        }
        
        // Additional check: User should not have any other PENDING claim for ANY kingdom
        $anyPendingClaim = KingClaim::where('user_id', $user->id)
                                  ->where('status', 'pending')
                                  ->exists();
        if ($anyPendingClaim) {
            Log::info("KingClaim Create: User {$user->id} already has another pending claim for a different kingdom.");
            return redirect()->route('dashboard')->with('info', 'You already have a pending kingship claim elsewhere. Please resolve it first.');
        }


        return view('king_claims.create', compact('kingdom'));
    }

    /**
     * Store a newly created king claim in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kingdom  $kingdom
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Kingdom $kingdom)
    {
        $user = Auth::user(); // In store(), $request->user() could also be used if preferred.

        // Check for active kingdom_join cooldown
        $activeKingdomCooldown = \App\Models\UserCooldown::where('user_id', $user->id)
                                            ->where('cooldown_type', 'kingdom_join')
                                            ->where('expires_at', '>', \Carbon\Carbon::now()) 
                                            ->exists();
        if ($activeKingdomCooldown) {
            Log::warning("King Claim Attempt Failed: User {$user->id} has an active 'kingdom_join' cooldown for store method.");
            return redirect()->route('dashboard')->with('error', 'You have an active cooldown from recently leaving a kingdom, and cannot claim a new one yet.');
        }

        // Authorization checks (repeated for safety, though middleware/form requests are better)
        if (!$user->is_king_candidate_verified) {
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'You are not verified as a king candidate.');
        }
        if ($user->isKing()) {
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'You are already a King.');
        }
        if (!$kingdom->is_active || $kingdom->king_user_id !== null) {
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'This kingdom cannot be claimed at this time.');
        }
        $existingClaim = KingClaim::where('user_id', $user->id)
                                  ->where('kingdom_id', $kingdom->id)
                                  ->where('status', 'pending')
                                  ->exists();
        if ($existingClaim) {
            return redirect()->route('kingdoms.show', $kingdom)->with('info', 'You already have a pending claim for this kingdom.');
        }
        
        // Additional check: User should not have any other PENDING claim for ANY kingdom
        $anyPendingClaim = KingClaim::where('user_id', $user->id)
                                  ->where('status', 'pending')
                                  ->exists();
        if ($anyPendingClaim) {
            return redirect()->route('dashboard')->with('info', 'You already have a pending kingship claim elsewhere. Please resolve it first.');
        }

        $request->validate([
            'reasoning' => 'nullable|string|max:2000',
        ]);

        try {
            $kingClaim = KingClaim::create([
                'user_id' => $user->id,
                'kingdom_id' => $kingdom->id,
                'reasoning' => $request->input('reasoning'),
                'status' => 'pending',
            ]);

            KingClaimSubmitted::dispatch($kingClaim);
            Log::info("KingClaim Stored: User {$user->id} submitted claim for Kingdom {$kingdom->id}. Claim ID: {$kingClaim->id}");

            return redirect()->route('kingdoms.show', $kingdom)->with('status', 'Your claim to rule has been submitted!');

        } catch (ValidationException $e) {
            Log::warning("KingClaim Store Validation Failed: User {$user->id}, Kingdom {$kingdom->id}. Errors: " . json_encode($e->errors()), ['exception' => $e]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("KingClaim Store Failed: User {$user->id}, Kingdom {$kingdom->id}. Error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('kingdoms.show', $kingdom)->with('error', 'Could not submit your claim due to a server error.');
        }
    }
}
