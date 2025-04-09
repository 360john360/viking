<?php

namespace App\Http\Controllers;

use App\Models\Kingdom;
use App\Models\KingdomJoinRequest;
use App\Models\KingdomMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- Add for authorize() helper

class KingdomJoinRequestController extends Controller
{
    use AuthorizesRequests; // <-- Add trait to use $this->authorize()

    /**
     * Display a listing of pending join requests for the King's kingdom.
     */
    public function index(): View
    {
        $king = Auth::user();
        $pendingRequests = collect();
        $kingdom = null;

        try {
            $kingdom = Kingdom::where('king_user_id', $king->id)->firstOrFail();
            $pendingRequests = KingdomJoinRequest::where('kingdom_id', $kingdom->id)
                                                 ->where('status', 'pending')
                                                 ->with('user')
                                                 ->orderBy('created_at', 'asc')
                                                 ->get();
             // Log::info("King Access: User ID {$king->id} viewed requests for Kingdom ID {$kingdom->id}. Found " . $pendingRequests->count() . " pending.");
        } catch (ModelNotFoundException $e) {
            Log::warning("Access Denied: User ID {$king->id} attempted to access kingdom requests but is not a King.");
        } catch (\Exception $e) {
            Log::error("Error fetching kingdom requests for User ID {$king->id}: " . $e->getMessage(), ['exception' => $e]);
        }

        return view('kingdom.requests.index', [
            'requests' => $pendingRequests,
            'kingdom' => $kingdom
        ]);
    }

    /**
     * Approve a kingdom join request.
     */
    public function approve(KingdomJoinRequest $joinRequest)
    {
        // --- Authorization via Policy ---
        $this->authorize('approve', $joinRequest); // <-- Checks policy, throws 403 if false

        // --- Pre-Checks --- (Authorization already done)
        $joinRequest->loadMissing(['user']); // Load user if not already loaded

        // Is the request actually pending?
        if ($joinRequest->status !== 'pending') {
            return redirect()->route('kingdom.management.requests.index')->with('info', 'Request already actioned.');
        }
        $userToApprove = $joinRequest->user;
        if (!$userToApprove) {
             Log::error("Approve Error: User for Request ID {$joinRequest->id} not found.");
             $joinRequest->update(['status' => 'rejected', 'reviewed_by_user_id' => Auth::id(), 'reviewed_at' => now(), 'message' => 'Applicant user not found.']);
             return redirect()->route('kingdom.management.requests.index')->with('error', 'Applicant user no longer exists.');
        }
         if (KingdomMembership::where('user_id', $userToApprove->id)->exists()) {
             Log::warning("Approve Fail: User {$userToApprove->id} already in a kingdom. Request {$joinRequest->id}.");
             $joinRequest->update(['status' => 'rejected', 'reviewed_by_user_id' => Auth::id(), 'reviewed_at' => now(), 'message' => 'User already belongs to a kingdom.']);
             return redirect()->route('kingdom.management.requests.index')->with('error', 'User already belongs to a kingdom. Request rejected.');
        }

        // --- Database Operations (Transaction) ---
        try {
            DB::transaction(function () use ($joinRequest, $userToApprove) { // $king not needed here anymore
                $joinRequest->update([
                    'status' => 'approved',
                    'reviewed_by_user_id' => Auth::id(), // Use Auth::id() directly
                    'reviewed_at' => Carbon::now(),
                ]);
                KingdomMembership::create([
                    'user_id' => $userToApprove->id,
                    'kingdom_id' => $joinRequest->kingdom_id,
                    'role' => 'member',
                    'joined_at' => Carbon::now(),
                ]);
                $userToApprove->update([
                    'current_kingdom_id' => $joinRequest->kingdom_id,
                    'current_tribe_id' => null,
                ]);
                Log::info("Request Approved: Request ID {$joinRequest->id} for User ID {$userToApprove->id} approved by King ID {Auth::id()}.");
                // TODO: Send notification (Raven) to $userToApprove later
            });
            return redirect()->route('kingdom.management.requests.index')->with('status', 'User approved and added to the kingdom successfully!');
        } catch (\Exception $e) {
            Log::error("Error approving join request ID {$joinRequest->id} by King ID {Auth::id()}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('kingdom.management.requests.index')->with('error', 'Database error occurred while approving. Please try again.');
        }
    }

    /**
     * Reject a kingdom join request.
     */
    public function reject(KingdomJoinRequest $joinRequest)
    {
        // --- Authorization via Policy ---
         $this->authorize('reject', $joinRequest); // <-- Checks policy, throws 403 if false

        // --- Pre-Checks --- (Authorization already done)
        // Is the request actually pending?
        if ($joinRequest->status !== 'pending') {
            return redirect()->route('kingdom.management.requests.index')->with('info', 'This request has already been actioned.');
        }

        // --- Database Update ---
        try {
            $joinRequest->update([
                'status' => 'rejected',
                'reviewed_by_user_id' => Auth::id(), // Use Auth::id() directly
                'reviewed_at' => Carbon::now(),
            ]);
            Log::info("Request Rejected: Request ID {$joinRequest->id} for User ID {$joinRequest->user_id} rejected by King ID {Auth::id()}.");
             // TODO: Send notification (Raven) to $joinRequest->user later
            return redirect()->route('kingdom.management.requests.index')->with('status', 'Request rejected successfully!');
        } catch (\Exception $e) {
            Log::error("Error rejecting join request ID {$joinRequest->id} by King ID {Auth::id()}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('kingdom.management.requests.index')->with('error', 'An error occurred while rejecting the request. Please try again.');
        }
    }
}