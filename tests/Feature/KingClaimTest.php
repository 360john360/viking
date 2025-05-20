<?php

use App\Models\User;
use App\Models\Kingdom;
use App\Models\KingClaim;
use App\Models\UserCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('verified user can submit a claim for an unclaimed kingdom', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    $this->actingAs($user)
        ->post(route('king_claims.store', $kingdom), ['reasoning' => 'I have a strong claim!'])
        ->assertRedirect(); // Or to a specific success route

    $this->assertDatabaseHas('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
        'status' => 'pending',
        'reasoning' => 'I have a strong claim!',
    ]);
});

test('unverified user cannot submit a claim', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => false]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    $this->actingAs($user)
        ->post(route('king_claims.store', $kingdom), ['reasoning' => 'My claim.'])
        ->assertRedirect(); // Or to a specific error route/back

    $this->assertDatabaseMissing('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
});

test('user cannot claim a kingdom if on kingdom_join cooldown', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    UserCooldown::factory()->create([
        'user_id' => $user->id,
        'cooldown_type' => 'kingdom_join',
        'expires_at' => Carbon::now()->addDays(3),
    ]);

    $this->actingAs($user)
        ->post(route('king_claims.store', $kingdom), ['reasoning' => 'My claim despite cooldown.'])
        ->assertRedirect(); // Or to a specific error route/back

    $this->assertDatabaseMissing('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
});

test('user cannot claim a kingdom that already has a king', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $otherKing = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $otherKing->id, 'is_active' => true]);

    $this->actingAs($user)
        ->post(route('king_claims.store', $kingdom), ['reasoning' => 'This kingdom is mine!'])
        ->assertRedirect();

    $this->assertDatabaseMissing('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
});

test('user cannot claim a kingdom if they are already a king of another kingdom', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdomUserIsKingOf = Kingdom::factory()->create(['king_user_id' => $user->id, 'is_active' => true]);
    // Update user model to reflect they are a king
    $user->current_kingdom_id = $kingdomUserIsKingOf->id; // Assuming this is how king status is also tracked for quick checks
    $user->save();


    $targetKingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    $this->actingAs($user)
        ->post(route('king_claims.store', $targetKingdom), ['reasoning' => 'One kingdom is not enough!'])
        ->assertRedirect();

    $this->assertDatabaseMissing('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $targetKingdom->id,
    ]);
});

test('user cannot have multiple pending claims for different kingdoms', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdom1 = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);
    $kingdom2 = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    // First claim
    KingClaim::factory()->create([
        'user_id' => $user->id,
        'kingdom_id' => $kingdom1->id,
        'status' => 'pending',
    ]);

    // Attempt second claim
    $this->actingAs($user)
        ->post(route('king_claims.store', $kingdom2), ['reasoning' => 'Claiming this one too!'])
        ->assertRedirect();

    $this->assertDatabaseMissing('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom2->id,
        'status' => 'pending', // Check that a new pending one wasn't added
    ]);
    // Ensure the first one is still there
    $this->assertDatabaseHas('king_claims', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom1->id,
        'status' => 'pending',
    ]);
});
