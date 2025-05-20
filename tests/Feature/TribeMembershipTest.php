<?php

use App\Models\User;
use App\Models\Kingdom;
use App\Models\Tribe;
use App\Models\KingdomMembership;
use App\Models\TribeMembership;
use App\Models\TribeJoinRequest;
use App\Models\UserCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('user who is member of parent kingdom can request to join tribe be approved and become member', function () {
    // Setup: King, Kingdom, Tribe Leader (can be same as King), User (member of Kingdom)
    $king = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $king->id, 'is_active' => true]);

    $tribeLeader = $king; // For simplicity, king is also tribe leader
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'leader_user_id' => $tribeLeader->id, 'is_active' => true]);

    $user = User::factory()->create();
    KingdomMembership::factory()->create(['user_id' => $user->id, 'kingdom_id' => $kingdom->id, 'role' => 'member']);
    $user->current_kingdom_id = $kingdom->id; // Manually set for test context
    $user->save();

    // User requests to join tribe
    $this->actingAs($user)->post(route('tribes.join.request', $tribe))
        ->assertRedirect();

    $this->assertDatabaseHas('tribe_join_requests', [
        'user_id' => $user->id,
        'tribe_id' => $tribe->id,
        'status' => 'pending',
    ]);

    $joinRequest = TribeJoinRequest::firstWhere(['user_id' => $user->id, 'tribe_id' => $tribe->id]);

    // Tribe Leader approves request
    $this->actingAs($tribeLeader)->patch(route('tribe-requests.approve', $joinRequest))
        ->assertRedirect();

    $this->assertDatabaseHas('tribe_join_requests', [
        'id' => $joinRequest->id,
        'status' => 'approved',
    ]);
    $this->assertDatabaseHas('tribe_memberships', [
        'user_id' => $user->id,
        'tribe_id' => $tribe->id,
    ]);

    $user->refresh();
    expect($user->current_tribe_id)->toBe($tribe->id);
});

test('users request to join a tribe can be rejected', function () {
    $king = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $king->id, 'is_active' => true]);
    $tribeLeader = $king;
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'leader_user_id' => $tribeLeader->id, 'is_active' => true]);
    $user = User::factory()->create();
    KingdomMembership::factory()->create(['user_id' => $user->id, 'kingdom_id' => $kingdom->id]);
    $user->current_kingdom_id = $kingdom->id;
    $user->save();

    $this->actingAs($user)->post(route('tribes.join.request', $tribe));
    $joinRequest = TribeJoinRequest::firstWhere(['user_id' => $user->id, 'tribe_id' => $tribe->id]);

    $this->actingAs($tribeLeader)->patch(route('tribe-requests.reject', $joinRequest))
        ->assertRedirect();

    $this->assertDatabaseHas('tribe_join_requests', [
        'id' => $joinRequest->id,
        'status' => 'rejected',
    ]);
    $this->assertDatabaseMissing('tribe_memberships', ['user_id' => $user->id, 'tribe_id' => $tribe->id]);
});

test('user cannot request to join a tribe if not a member of the parent kingdom', function () {
    $user = User::factory()->create(); // User is NOT a member of the kingdom
    $kingdom = Kingdom::factory()->create(['is_active' => true]);
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'is_active' => true]);

    $response = $this->actingAs($user)->post(route('tribes.join.request', $tribe));
    $response->assertRedirect(); // Should redirect back or to dashboard with error

    $this->assertDatabaseMissing('tribe_join_requests', [
        'user_id' => $user->id,
        'tribe_id' => $tribe->id,
    ]);
});

test('user can leave a tribe', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'is_active' => true]);

    // Make user member of kingdom and tribe
    KingdomMembership::factory()->create(['user_id' => $user->id, 'kingdom_id' => $kingdom->id]);
    $user->current_kingdom_id = $kingdom->id;
    TribeMembership::factory()->create(['user_id' => $user->id, 'tribe_id' => $tribe->id]);
    $user->current_tribe_id = $tribe->id;
    $user->save();

    $this->actingAs($user)->post(route('tribe.leave'))
        ->assertRedirect();

    $this->assertDatabaseMissing('tribe_memberships', ['user_id' => $user->id, 'tribe_id' => $tribe->id]);
    $user->refresh();
    expect($user->current_tribe_id)->toBeNull();
    expect($user->current_kingdom_id)->toBe($kingdom->id); // Kingdom membership should persist

    $this->assertDatabaseHas('user_cooldowns', [
        'user_id' => $user->id,
        'cooldown_type' => 'tribe_join',
    ]);
});

test('user cannot request to join a tribe if on tribe_join cooldown', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);
    // User is member of kingdom
    KingdomMembership::factory()->create(['user_id' => $user->id, 'kingdom_id' => $kingdom->id]);
    $user->current_kingdom_id = $kingdom->id;
    $user->save();

    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'is_active' => true]);

    UserCooldown::factory()->create([
        'user_id' => $user->id,
        'cooldown_type' => 'tribe_join',
        'expires_at' => Carbon::now()->addDays(3),
    ]);

    $response = $this->actingAs($user)->post(route('tribes.join.request', $tribe));
    $response->assertRedirect();

    $this->assertDatabaseMissing('tribe_join_requests', [
        'user_id' => $user->id,
        'tribe_id' => $tribe->id,
    ]);
});
