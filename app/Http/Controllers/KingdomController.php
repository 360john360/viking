<?php

namespace App\Http\Controllers;

use App\Models\Kingdom;
use App\Models\KingdomJoinRequest;
use App\Models\UserCooldown;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;

class KingdomController extends Controller
{
    use AuthorizesRequests;

    // Index method...
    public function index(): View
    {
        $this->authorize('viewAny', Kingdom::class);
        $kingdoms = Kingdom::where('is_active', true)->orderBy('name')->paginate(15);
        return view('kingdoms.index', compact('kingdoms'));
    }

    // Create method...
    public function create(): View
    {
         $this->authorize('create', Kingdom::class);
        return view('kingdoms.create');
    }

    // Store method...
    public function store(Request $request): RedirectResponse
    {
         $this->authorize('create', Kingdom::class);
         $validatedData = $request->validate([
             'name' => ['required', 'string', 'max:255', 'unique:kingdoms,name'],
             'description' => ['nullable', 'string'],
         ]);
         try {
             $kingdom = Kingdom::create([
                 'name' => $validatedData['name'],
                 'slug' => Str::slug($validatedData['name']),
                 'description' => $validatedData['description'],
                 'is_active' => true,
                 'king_user_id' => null,
             ]);
            Log::info("Kingdom Created: Admin User ID {Auth::id()} created Kingdom ID {$kingdom->id} ('{$kingdom->name}').");
            return redirect()->route('kingdoms.show', $kingdom)->with('status', 'Kingdom created successfully!');
         } catch (Exception $e) {
             Log::error("Error creating kingdom by Admin User ID {Auth::id()}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->route('kingdoms.create')->withInput()->with('error', 'Failed to create kingdom. Please try again.');
         }
    }

    // requestToJoin method...
     public function requestToJoin(Kingdom $kingdom): RedirectResponse
    {
        $this->authorize('view', $kingdom);
        $user = Auth::user();
        if ($user->current_kingdom_id !== null) { return redirect()->back()->with('error', 'You are already in a kingdom.'); }
        $activeCooldown = UserCooldown::where('user_id', $user->id)->where('cooldown_type', 'kingdom_join')->where('expires_at', '>', Carbon::now())->exists();
        if ($activeCooldown) { return redirect()->back()->with('error', 'You have an active cooldown.'); }
        $existingRequest = KingdomJoinRequest::where('user_id', $user->id)->where('kingdom_id', $kingdom->id)->where('status', 'pending')->exists();
        if ($existingRequest) { return redirect()->back()->with('info', 'Your request is already pending.'); }
        try {
            KingdomJoinRequest::create(['user_id' => $user->id, 'kingdom_id' => $kingdom->id,]);
            return redirect()->back()->with('status', 'Request to join submitted successfully!');
        } catch (Exception $e) {
             Log::error("Error creating join request: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->back()->with('error', 'Could not submit join request.');
        }
    }

    // Show method...
    public function show(Kingdom $kingdom): View
    {
         $this->authorize('view', $kingdom);
        return view('kingdoms.show', compact('kingdom'));
    }

    // Edit method...
    public function edit(Kingdom $kingdom): View
    {
         $this->authorize('update', $kingdom);
         return view('kingdoms.edit', compact('kingdom'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kingdom $kingdom): RedirectResponse
    {
         $this->authorize('update', $kingdom);
         $validatedData = $request->validate([
             'name' => ['required', 'string', 'max:255', Rule::unique('kingdoms')->ignore($kingdom->id)],
             'description' => ['nullable', 'string'],
             'is_active' => ['required', 'boolean'],
         ]);
         try {
             $updateData = [
                 'name' => $validatedData['name'],
                 'description' => $validatedData['description'],
                 'is_active' => $validatedData['is_active'],
             ];
             if ($kingdom->name !== $validatedData['name']) {
                 $updateData['slug'] = Str::slug($validatedData['name']);
             }

             $kingdom->update($updateData);

            Log::info("Kingdom Updated: Admin User ID {Auth::id()} updated Kingdom ID {$kingdom->id}. Active status: " . $validatedData['is_active']);

            // --- MODIFIED REDIRECT ---
            // If kingdom is now inactive, redirect to index. Otherwise, redirect to show page.
            if ($kingdom->is_active) {
                return redirect()->route('kingdoms.show', $kingdom)->with('status', 'Kingdom updated successfully!');
            } else {
                return redirect()->route('kingdoms.index')->with('status', 'Kingdom deactivated successfully!');
            }
            // --- END MODIFIED REDIRECT ---

         } catch (Exception $e) {
             Log::error("Error updating kingdom ID {$kingdom->id} by Admin User ID {Auth::id()}: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->route('kingdoms.edit', $kingdom)->withInput()->with('error', 'Failed to update kingdom. Please try again.');
         }
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     * Using deactivation via is_active instead.
     */
    public function destroy(Kingdom $kingdom): RedirectResponse
    {
         $this->authorize('delete', $kingdom);
         // Deletion disabled
         return redirect()->route('kingdoms.index')->with('info', 'Kingdom deletion is disabled. Use activation status instead.');
    }
}