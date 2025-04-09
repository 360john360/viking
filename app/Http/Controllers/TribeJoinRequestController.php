<?php

namespace App\Http\Controllers;

// Required Models & Facades
use App\Models\Tribe;
use App\Models\TribeJoinRequest;
use App\Models\User;
use App\Models\TribeMembership;
use App\Policies\TribeJoinRequestPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TribeJoinRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display pending join requests for the tribe(s) the current user manages.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $tribe = $user->tribesAsLeader()->where('is_active', true)->with('kingdom:id,name')->first();

        if (!$tribe) {
            // Add Officer check later if needed
            Log::info("User {$user->id} tried accessing tribe requests but doesn't manage an active tribe.");
            return redirect()->route('dashboard')->with('error', 'You do not currently manage any active tribes.');
        }

        // Authorization: Use the TribePolicy@viewAnyRequests
        $this->authorize('viewAnyRequests', $tribe);

        // --- DEBUG dd() REMOVED ---

        // Fetch pending requests
        $requests = TribeJoinRequest::where('tribe_id', $tribe->id)
                                    ->where('status', 'pending')
                                    ->with('user:id,name')
                                    ->orderBy('created_at', 'asc')
                                    ->get();

        return view('tribes.requests.index', compact('tribe', 'requests'));
    }

    /**
     * Approve a tribe join request.
     */
    public function approve(TribeJoinRequest $tribeJoinRequest): RedirectResponse
    {
        $this->authorize('approve', $tribeJoinRequest);
        $tribeJoinRequest->loadMissing(['user', 'tribe']);
        $applicant = $tribeJoinRequest->user;
        $tribe = $tribeJoinRequest->tribe;
        $approver = Auth::user();

        // Pre-Checks...
        if ($tribeJoinRequest->status !== 'pending') { return redirect()->route('tribe-requests.index')->with('info', 'Request already actioned.'); }
        if (!$applicant) { /* Handle missing user */ try { $tribeJoinRequest->update(['status'=>'rejected', /*...*/]); } catch (Exception $e){} return redirect()->route('tribe-requests.index')->with('error', 'Applicant user no longer exists.'); }
        if ($applicant->current_tribe_id !== null) { /* Handle already in tribe */ $tribeJoinRequest->update(['status'=>'rejected', /*...*/]); return redirect()->route('tribe-requests.index')->with('error', 'User already belongs to a tribe.'); }
        if ($applicant->current_kingdom_id !== $tribe->kingdom_id) { /* Handle wrong kingdom */ $tribeJoinRequest->update(['status'=>'rejected', /*...*/]); return redirect()->route('tribe-requests.index')->with('error', 'Applicant is not in the required kingdom.'); }

        DB::beginTransaction();
        try {
            $tribeJoinRequest->update([ 'status' => 'approved', 'reviewed_by_user_id' => $approver->id, 'reviewed_at' => now(), ]);
            TribeMembership::create([ 'user_id' => $applicant->id, 'tribe_id' => $tribe->id, 'role' => 'member', 'joined_at' => now(), ]);
            $applicant->update(['current_tribe_id' => $tribe->id]);
            DB::commit();
            Log::info("Tribe Join Request Approved: Request ID {$tribeJoinRequest->id}...");
            return redirect()->route('tribe-requests.index')->with('status', 'Petition approved. Warrior added to the tribe!');
        } catch (Exception $e) {
             DB::rollBack();
             Log::error("Error approving tribe join request ID {$tribeJoinRequest->id}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->back()->with('error', 'Failed to approve join request due to a server error.');
        }
    }

    /**
     * Reject a tribe join request.
     */
    public function reject(TribeJoinRequest $tribeJoinRequest): RedirectResponse
    {
        $this->authorize('reject', $tribeJoinRequest);
        $approver = Auth::user();
        if ($tribeJoinRequest->status !== 'pending') { return redirect()->route('tribe-requests.index')->with('info', 'This request has already been actioned.'); }
        try {
             $tribeJoinRequest->update([ 'status' => 'rejected', 'reviewed_by_user_id' => $approver->id, 'reviewed_at' => now(), ]);
             Log::info("Tribe Join Request Rejected: Request ID {$tribeJoinRequest->id}...");
             return redirect()->route('tribe-requests.index')->with('status', 'Petition rejected successfully.');
        } catch (Exception $e) {
             Log::error("Error rejecting tribe join request ID {$tribeJoinRequest->id}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->back()->with('error', 'Failed to reject join request due to a server error.');
        }
    }
}