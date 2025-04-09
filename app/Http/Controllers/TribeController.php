<?php

namespace App\Http\Controllers;

use App\Models\Tribe;
use App\Models\Kingdom;
use App\Models\User;                // Added
use App\Models\TribeMembership;     // Added
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Added
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Added
use Illuminate\Support\Facades\DB;   // Added for Transaction
use Exception;

class TribeController extends Controller
{
    use AuthorizesRequests;

    // Index method (no changes)
    public function index(): View
    {
        $this->authorize('viewAny', Tribe::class);
        $tribes = Tribe::whereHas('kingdom', function ($query) { $query->where('is_active', true); })
                        ->with(['kingdom:id,name', 'leader:id,name'])
                        ->orderBy('kingdom_id')->orderBy('name')
                        ->paginate(20);
        return view('tribes.index', compact('tribes'));
    }

    // Create method (no changes)
    public function create(): View
    {
        $this->authorize('create', Tribe::class);
        $kingdoms = Kingdom::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('tribes.create', compact('kingdoms'));
    }

    // Store method (no changes)
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tribe::class);
        $validatedData = $request->validate([ /* ... */ ]); // Validation as before
        try {
            $tribe = Tribe::create([ /* ... */ ]); // Creation logic as before
            Log::info("Tribe Created: Admin User ID {Auth::id()} created Tribe ID {$tribe->id} ...");
            return redirect()->route('tribes.show', $tribe)->with('status', 'Tribe established successfully!');
        } catch (Exception $e) {
             Log::error("Error creating tribe...: " . $e->getMessage(), ['exception' => $e]);
             return redirect()->route('tribes.create')->withInput()->with('error', 'Failed to establish tribe.');
        }
    }

    // Show method (no changes)
    public function show(Tribe $tribe): View
    {
         $this->authorize('view', $tribe);
         $tribe->load(['kingdom:id,name', 'leader:id,name']);
         return view('tribes.show', compact('tribe'));
    }

    // Edit method (no changes from Step 148)
    public function edit(Tribe $tribe): View
    {
         $this->authorize('update', $tribe);
         $kingdoms = Kingdom::where('is_active', true)->orderBy('name')->pluck('name', 'id');
         $potentialLeaders = User::where('current_kingdom_id', $tribe->kingdom_id)
                                ->orderBy('name')
                                ->pluck('name', 'id');
         return view('tribes.edit', compact('tribe', 'kingdoms', 'potentialLeaders'));
    }

    /**
     * Update the specified resource in storage.
     * REFACTORED to handle leader assignment and membership correctly.
     */
    public function update(Request $request, Tribe $tribe): RedirectResponse
    {
         $this->authorize('update', $tribe);

         // Define validation rules separately for clarity
         $validationRules = [
             'name' => ['required', 'string', 'max:255', Rule::unique('tribes')->where('kingdom_id', $request->input('kingdom_id'))->ignore($tribe->id)],
             'description' => ['nullable', 'string'],
             'kingdom_id' => ['required', 'integer', Rule::exists('kingdoms', 'id')->where('is_active', true)],
             'is_active' => ['required', 'boolean'],
             'leader_user_id' => [
                 'nullable', 'integer',
                 Rule::exists('users', 'id')->where('current_kingdom_id', $request->input('kingdom_id'))
             ],
         ];

         // Add a custom validation rule to ensure the new leader isn't already leading/member of another tribe?
         // For now, we handle this check inside the transaction logic below.

         $validatedData = $request->validate($validationRules);

         $newLeaderId = $validatedData['leader_user_id'] ? (int)$validatedData['leader_user_id'] : null;
         $oldLeaderId = $tribe->leader_user_id ? (int)$tribe->leader_user_id : null; // Get ID before update
         $newKingdomId = (int)$validatedData['kingdom_id'];

         DB::beginTransaction(); // Start Transaction
         try {
             // Prepare basic tribe update data
             $updateData = [
                 'name' => $validatedData['name'],
                 'description' => $validatedData['description'],
                 'kingdom_id' => $newKingdomId,
                 'is_active' => $validatedData['is_active'],
                 'leader_user_id' => $newLeaderId, // Set the new leader ID
             ];
             if ($tribe->name !== $validatedData['name']) {
                 $updateData['slug'] = Str::slug($validatedData['name']);
             }

             // Step 1: Update the main Tribe record
             $tribe->update($updateData);

             // Step 2: Handle membership changes IF the leader has changed
             if ($newLeaderId !== $oldLeaderId) {

                 // A) Demote the OLD leader (if there was one)
                 if ($oldLeaderId !== null) {
                     TribeMembership::where('tribe_id', $tribe->id)
                                    ->where('user_id', $oldLeaderId)
                                    ->where('role', 'leader') // Be specific
                                    ->update(['role' => 'member']); // Demote to member
                     // Note: We don't change the old leader's current_tribe_id here
                 }

                 // B) Promote/Add the NEW leader (if one is selected)
                 if ($newLeaderId !== null) {
                     // B.1 - Check if the new leader is already in THIS tribe
                     $newLeaderMembership = TribeMembership::where('user_id', $newLeaderId)
                                                           ->where('tribe_id', $tribe->id)
                                                           ->first();
                     if ($newLeaderMembership) {
                         // Already a member, just update role
                         $newLeaderMembership->update(['role' => 'leader']);
                     } else {
                         // Not a member of THIS tribe yet. Check if member of ANOTHER tribe.
                         if (TribeMembership::where('user_id', $newLeaderId)->exists()) {
                             // Throw exception to rollback transaction
                             throw new Exception("Cannot assign leader: User {$newLeaderId} already belongs to another tribe.");
                         }

                         // Create new membership record for the leader
                         TribeMembership::create([
                             'user_id' => $newLeaderId,
                             'tribe_id' => $tribe->id,
                             'role' => 'leader',
                             'joined_at' => now(),
                         ]);
                     }

                     // B.2 - Update the new leader's User record
                     User::find($newLeaderId)->update(['current_tribe_id' => $tribe->id]);
                 }
             }

             DB::commit(); // Commit Transaction

             Log::info("Tribe Updated: Admin/King User ID {Auth::id()} updated Tribe ID {$tribe->id}. Leader ID: {$newLeaderId}. Active: {$validatedData['is_active']}");

             // Conditional redirect
             // Must refresh tribe model to get potentially updated kingdom relationship status
             $tribe->refresh();
             if ($tribe->is_active && $tribe->kingdom?->is_active) {
                 return redirect()->route('tribes.show', $tribe)->with('status', 'Tribe updated successfully!');
             } else {
                 return redirect()->route('tribes.index')->with('status', 'Tribe updated (now inactive or in inactive kingdom).');
             }

         } catch (Exception $e) {
             DB::rollBack(); // Rollback on any error
             Log::error("Error updating tribe ID {$tribe->id} by Admin/King User ID {Auth::id()}: " . $e->getMessage(), ['exception' => $e]);
             // Redirect back to edit form with errors and old input
             // Use with() chaining for multiple flashed data items
             return redirect()->route('tribes.edit', $tribe)->withInput()->with('error', 'Failed to update tribe: ' . $e->getMessage());
         }
    }

    /**
     * Remove the specified resource from storage.
     * (Using Deactivation instead)
     */
    public function destroy(Tribe $tribe): RedirectResponse
    {
         $this->authorize('delete', $tribe);
         return redirect()->route('tribes.index')->with('info', 'Tribe deletion is disabled. Use activation status instead.');
    }
}