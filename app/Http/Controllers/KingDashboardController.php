<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Kingdom;
use App\Models\KingdomJoinRequest;

class KingDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Confirm the user is a King
        $kingdom = $user->kingdomsAsKing()->where('is_active', true)->with([
            'tribes.leader',
        ])->firstOrFail();

        // Get pending join requests for this kingdom
        $pendingRequests = KingdomJoinRequest::where('kingdom_id', $kingdom->id)
            ->where('status', 'pending')
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('king.dashboard', [
            'kingsKingdom' => $kingdom,
            'pendingKingdomJoinRequests' => $pendingRequests,
        ]);
    }
}
