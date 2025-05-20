<?php

use App\Models\User;
use App\Models\Kingdom;
use App\Models\KingdomJoinRequest;
use App\Models\KingdomMembership;
use App\Models\UserCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('user can request to join a kingdom, be approved, and become a member', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);
    $king = User::factory()->create(); // User to act as King

    // Assign king to the kingdom for approval purposes
    $kingdom->king_user_id = $king->id;
    $kingdom->save();

    // User requests to join
    $this->actingAs($user)->post(route('kingdoms.join.request', $kingdom))
        ->assertRedirect(); // Or specific redirect

    $this->assertDatabaseHas('kingdom_join_requests', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
        'status' => 'pending',
    ]);

    $joinRequest = KingdomJoinRequest::firstWhere([
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);

    // King approves request
    $this->actingAs($king)->patch(route('kingdom.management.requests.approve', $joinRequest))
        ->assertRedirect(); // Or specific redirect

    $this->assertDatabaseHas('kingdom_join_requests', [
        'id' => $joinRequest->id,
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('kingdom_memberships', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);

    $user->refresh();
    expect($user->current_kingdom_id)->toBe($kingdom->id);
});

test('users request to join a kingdom can be rejected', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);
    $king = User::factory()->create();
    $kingdom->king_user_id = $king->id;
    $kingdom->save();

    $this->actingAs($user)->post(route('kingdoms.join.request', $kingdom));
    $joinRequest = KingdomJoinRequest::firstWhere(['user_id' => $user->id, 'kingdom_id' => $kingdom->id]);

    $this->actingAs($king)->patch(route('kingdom.management.requests.reject', $joinRequest))
        ->assertRedirect();

    $this->assertDatabaseHas('kingdom_join_requests', [
        'id' => $joinRequest->id,
        'status' => 'rejected',
    ]);

    $this->assertDatabaseMissing('kingdom_memberships', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
});

test('user can leave a kingdom', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);

    // Make user a member
    KingdomMembership::factory()->create([
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
    $user->current_kingdom_id = $kingdom->id;
    $user->save();

    $this->actingAs($user)->post(route('kingdom.leave'))
        ->assertRedirect();

    $this->assertDatabaseMissing('kingdom_memberships', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);

    $user->refresh();
    expect($user->current_kingdom_id)->toBeNull();

    $this->assertDatabaseHas('user_cooldowns', [
        'user_id' => $user->id,
        'cooldown_type' => 'kingdom_join',
    ]);
});

test('user cannot request to join a kingdom if on kingdom_join cooldown', function () {
    $user = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['is_active' => true]);

    UserCooldown::factory()->create([
        'user_id' => $user->id,
        'cooldown_type' => 'kingdom_join',
        'expires_at' => Carbon::now()->addDays(3),
    ]);

    $response = $this->actingAs($user)->post(route('kingdoms.join.request', $kingdom));
    // Expect a redirect back, possibly with an error in session
    $response->assertRedirect(); // More specific check if possible, e.g. to dashboard or back
    // It should not create a join request
    $this->assertDatabaseMissing('kingdom_join_requests', [
        'user_id' => $user->id,
        'kingdom_id' => $kingdom->id,
    ]);
});
