<?php

// Required Controllers
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KingdomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KingdomJoinRequestController;
use App\Http\Controllers\KingdomMembershipController;
use App\Http\Controllers\TribeController;
use App\Http\Controllers\TribeMembershipController;
use App\Http\Controllers\TribeJoinRequestController; // Ensures this is imported

// Required Models & Facades for Dashboard Route Closure
use Illuminate\Support\Facades\Auth;
use App\Models\Kingdom;
use App\Models\KingdomJoinRequest;
use App\Models\TribeJoinRequest; // Ensures this is imported
use App\Http\Controllers\KingDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Welcome Route
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route - Fetches data needed for King/Thane panels
Route::get('/dashboard', function () {
    $user = Auth::user();
    // Initialize variables FIRST
    $pendingKingdomJoinRequests = collect();
    $kingsKingdom = null;
    $pendingTribeJoinRequests = collect();
    $thanesTribe = null;

    // Check if user is potentially a king AND load kingdom if so
    if ($user->isKing()) { // Assumes isKing() helper exists on User Model
        $kingsKingdom = $user->kingdomsAsKing()->where('is_active', true)->first(); // Use relationship
        if ($kingsKingdom) {
             // Fetch pending kingdom requests for THIS kingdom
             $pendingKingdomJoinRequests = KingdomJoinRequest::where('kingdom_id', $kingsKingdom->id)
                                                 ->where('status', 'pending')
                                                 ->with('user:id,name')
                                                 ->orderBy('created_at', 'asc')
                                                 ->get();
        }
    }

    // Check if user is potentially a Thane AND load their first active tribe if so
    if ($user->isThaneOfAnyTribe()) { // Assumes isThaneOfAnyTribe() helper exists on User Model
        $thanesTribe = $user->tribesAsLeader()->where('is_active', true)->with('kingdom:id,name')->first();
        if ($thanesTribe) {
            // Fetch pending tribe requests for THIS tribe
            $pendingTribeJoinRequests = TribeJoinRequest::where('tribe_id', $thanesTribe->id)
                                         ->where('status', 'pending')
                                         ->with('user:id,name')
                                         ->orderBy('created_at', 'asc')
                                         ->get();
        }
    }

    // Pass ALL variables (even if empty collections/null) to the view
    return view('dashboard', [
        'pendingKingdomJoinRequests' => $pendingKingdomJoinRequests,
        'kingsKingdom' => $kingsKingdom,
        'pendingTribeJoinRequests' => $pendingTribeJoinRequests,
        'thanesTribe' => $thanesTribe
    ]);

})->middleware(['auth', 'verified'])->name('dashboard');


// Other Authenticated Routes
Route::middleware('auth')->group(function () {

    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kingdom Resource routes (excluding destroy)
    Route::resource('kingdoms', KingdomController::class)->except(['destroy']);

    // Kingdom Join/Leave routes
    Route::post('/kingdoms/{kingdom}/join', [KingdomController::class, 'requestToJoin'])->name('kingdoms.join.request');
    Route::post('/kingdom/leave', [KingdomMembershipController::class, 'leave'])->name('kingdom.leave');

    // Tribe Resource Routes (excluding destroy)
    Route::resource('tribes', TribeController::class)->except(['destroy']);

    // Tribe Join/Leave routes
    Route::post('/tribes/{tribe}/join', [TribeMembershipController::class, 'requestToJoin'])->name('tribes.join.request');
    // TODO: Route::post('/tribe/leave', [TribeMembershipController::class, 'leaveTribe'])->name('tribe.leave');

    // Kingdom Join Request Management Routes (for King) - Separate Page
    Route::prefix('kingdom-management')->name('kingdom.management.')->group(function () {
            Route::get('/requests', [KingdomJoinRequestController::class, 'index'])->name('requests.index');
            Route::patch('/requests/{joinRequest}/approve', [KingdomJoinRequestController::class, 'approve'])->name('requests.approve');
            Route::patch('/requests/{joinRequest}/reject', [KingdomJoinRequestController::class, 'reject'])->name('requests.reject');
    });
    Route::get('/king/dashboard', [KingDashboardController::class, 'index'])->name('king.dashboard');


    // Tribe Join Request Management Routes (for Thane/Officer) - Separate Page
    Route::prefix('tribe-management')->name('tribe-requests.')->group(function () {
        Route::get('/requests', [TribeJoinRequestController::class, 'index'])->name('index');
        Route::patch('/requests/{tribeJoinRequest}/approve', [TribeJoinRequestController::class, 'approve'])->name('approve');
        Route::patch('/requests/{tribeJoinRequest}/reject', [TribeJoinRequestController::class, 'reject'])->name('reject');
    });

}); // End of Route::middleware('auth')->group()

// Breeze Auth Routes
require __DIR__.'/auth.php';