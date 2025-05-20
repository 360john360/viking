<?php

use App\Models\User;
use App\Models\Kingdom;
use App\Models\Tribe;
use App\Models\KingdomMembership;
use App\Models\TribeMembership;
use App\Models\KingdomJoinRequest;
use App\Models\TribeJoinRequest;
use App\Models\KingClaim;
use App\Models\UserCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

// --- Test King Dashboard Access ---
test('king can access king dashboard', function () {
    $king = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $king->id, 'is_active' => true]);
    // Ensure user's isKing() helper would return true.
    // If isKing() relies on current_kingdom_id, set it:
    // $king->current_kingdom_id = $kingdom->id;
    // $king->save();


    $this->actingAs($king)
        ->get(route('king.dashboard'))
        ->assertOk();
});

test('regular user cannot access king dashboard', function () {
    $user = User::factory()->create(); // Not a king

    $this->actingAs($user)
        ->get(route('king.dashboard'))
        ->assertForbidden();
});

// --- Test Kingdom Creation Access ---
test('site admin can access kingdom create page', function () {
    $admin = User::factory()->create(['is_site_admin' => true]);

    $this->actingAs($admin)
        ->get(route('kingdoms.create'))
        ->assertOk();
});

test('non site admin cannot access kingdom create page', function () {
    $user = User::factory()->create(['is_site_admin' => false]);

    $this->actingAs($user)
        ->get(route('kingdoms.create'))
        ->assertForbidden();
});

// --- Test Tribe Creation Access ---
test('site admin can access tribe create page', function () {
    $admin = User::factory()->create(['is_site_admin' => true]);

    $this->actingAs($admin)
        ->get(route('tribes.create'))
        ->assertOk();
});

test('king can access tribe create page', function () {
    $king = User::factory()->create();
    Kingdom::factory()->create(['king_user_id' => $king->id, 'is_active' => true]);
    // $king->current_kingdom_id = $kingdom->id; // If isKing() helper needs it
    // $king->save();

    $this->actingAs($king)
        ->get(route('tribes.create'))
        ->assertOk();
});

test('regular user cannot access tribe create page', function () {
    $user = User::factory()->create(['is_site_admin' => false]);
    // Ensure user is not also a king by chance if factories are simple
    // No need if User factory doesn't make kings by default

    $this->actingAs($user)
        ->get(route('tribes.create'))
        ->assertForbidden();
});

// --- Test King Claim Form Access ---
test('verified user can access king claim form for claimable kingdom', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('king_claims.create', $kingdom))
        ->assertOk();
});

test('non verified user cannot access king claim form', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => false]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('king_claims.create', $kingdom))
        ->assertRedirect(); // Controller redirects with error
});

test('verified user cannot access king claim form for claimed kingdom', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $otherKing = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $otherKing->id, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('king_claims.create', $kingdom))
        ->assertRedirect(); // Controller redirects with error
});

test('verified user on kingdom_join cooldown cannot access king claim form', function () {
    $user = User::factory()->create(['is_king_candidate_verified' => true]);
    $kingdom = Kingdom::factory()->create(['king_user_id' => null, 'is_active' => true]);
    UserCooldown::factory()->create([
        'user_id' => $user->id,
        'cooldown_type' => 'kingdom_join',
        'expires_at' => Carbon::now()->addDays(1),
    ]);

    $this->actingAs($user)
        ->get(route('king_claims.create', $kingdom))
        ->assertRedirect(); // Controller redirects with error
});


// --- Test Kingdom Join Request Approval/Rejection ---
function setupKingdomJoinRequestScenario() {
    $applicant = User::factory()->create();
    $king = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $king->id, 'is_active' => true]);
    $moderator = User::factory()->create();
    KingdomMembership::factory()->create([
        'user_id' => $moderator->id,
        'kingdom_id' => $kingdom->id,
        'role' => 'moderator',
    ]);
    $randomUser = User::factory()->create();
    $joinRequest = KingdomJoinRequest::factory()->create([
        'user_id' => $applicant->id,
        'kingdom_id' => $kingdom->id,
        'status' => 'pending',
    ]);
    return compact('applicant', 'king', 'kingdom', 'moderator', 'randomUser', 'joinRequest');
}

test('king can approve kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['king'])
        ->patch(route('kingdom.management.requests.approve', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'approved']);
    $this->assertDatabaseHas('kingdom_memberships', ['user_id' => $data['applicant']->id, 'kingdom_id' => $data['kingdom']->id]);
});

test('kingdom moderator can approve kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['moderator'])
        ->patch(route('kingdom.management.requests.approve', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'approved']);
    $this->assertDatabaseHas('kingdom_memberships', ['user_id' => $data['applicant']->id, 'kingdom_id' => $data['kingdom']->id]);
});

test('applicant cannot approve own kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['applicant'])
        ->patch(route('kingdom.management.requests.approve', $data['joinRequest']))
        ->assertForbidden();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'pending']);
});

test('random user cannot approve kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['randomUser'])
        ->patch(route('kingdom.management.requests.approve', $data['joinRequest']))
        ->assertForbidden();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'pending']);
});

test('king can reject kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['king'])
        ->patch(route('kingdom.management.requests.reject', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'rejected']);
    $this->assertDatabaseMissing('kingdom_memberships', ['user_id' => $data['applicant']->id, 'kingdom_id' => $data['kingdom']->id]);
});

test('kingdom moderator can reject kingdom join request', function () {
    $data = setupKingdomJoinRequestScenario();
    $this->actingAs($data['moderator'])
        ->patch(route('kingdom.management.requests.reject', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('kingdom_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'rejected']);
});


// --- Test Tribe Join Request Approval/Rejection ---
function setupTribeJoinRequestScenario() {
    $kingdomKing = User::factory()->create(); // User to be king of the kingdom
    $kingdom = Kingdom::factory()->create(['king_user_id' => $kingdomKing->id, 'is_active' => true]);

    $applicant = User::factory()->create();
    // Make applicant a member of the parent kingdom
    KingdomMembership::factory()->create(['user_id' => $applicant->id, 'kingdom_id' => $kingdom->id]);
    $applicant->current_kingdom_id = $kingdom->id;
    $applicant->save();

    $tribeLeader = User::factory()->create();
    // Make tribe leader a member of the parent kingdom (often a requirement)
    KingdomMembership::factory()->create(['user_id' => $tribeLeader->id, 'kingdom_id' => $kingdom->id]);
    $tribeLeader->current_kingdom_id = $kingdom->id;
    $tribeLeader->save();
    
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'leader_user_id' => $tribeLeader->id, 'is_active' => true]);
    
    $tribeOfficer = User::factory()->create();
    // Make officer a member of the parent kingdom and the tribe
    KingdomMembership::factory()->create(['user_id' => $tribeOfficer->id, 'kingdom_id' => $kingdom->id]);
    $tribeOfficer->current_kingdom_id = $kingdom->id;
    TribeMembership::factory()->create([
        'user_id' => $tribeOfficer->id,
        'tribe_id' => $tribe->id,
        'role' => 'officer',
    ]);
    $tribeOfficer->current_tribe_id = $tribe->id;
    $tribeOfficer->save();

    $randomUser = User::factory()->create();
    KingdomMembership::factory()->create(['user_id' => $randomUser->id, 'kingdom_id' => $kingdom->id]); // Random user also in kingdom
    $randomUser->current_kingdom_id = $kingdom->id;
    $randomUser->save();


    $joinRequest = TribeJoinRequest::factory()->create([
        'user_id' => $applicant->id,
        'tribe_id' => $tribe->id,
        'status' => 'pending',
    ]);
    return compact('applicant', 'kingdom', 'tribe', 'tribeLeader', 'tribeOfficer', 'randomUser', 'joinRequest');
}

test('tribe leader can approve tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['tribeLeader'])
        ->patch(route('tribe-requests.approve', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('tribe_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'approved']);
    $this->assertDatabaseHas('tribe_memberships', ['user_id' => $data['applicant']->id, 'tribe_id' => $data['tribe']->id]);
});

test('tribe officer can approve tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['tribeOfficer'])
        ->patch(route('tribe-requests.approve', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('tribe_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'approved']);
});

test('applicant cannot approve own tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['applicant'])
        ->patch(route('tribe-requests.approve', $data['joinRequest']))
        ->assertForbidden();
    $this->assertDatabaseHas('tribe_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'pending']);
});

test('random user cannot approve tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['randomUser'])
        ->patch(route('tribe-requests.approve', $data['joinRequest']))
        ->assertForbidden(); // Or redirect if your controller handles it that way
});

test('tribe leader can reject tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['tribeLeader'])
        ->patch(route('tribe-requests.reject', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('tribe_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'rejected']);
    $this->assertDatabaseMissing('tribe_memberships', ['user_id' => $data['applicant']->id, 'tribe_id' => $data['tribe']->id]);
});

test('tribe officer can reject tribe join request', function () {
    $data = setupTribeJoinRequestScenario();
    $this->actingAs($data['tribeOfficer'])
        ->patch(route('tribe-requests.reject', $data['joinRequest']))
        ->assertRedirect();
    $this->assertDatabaseHas('tribe_join_requests', ['id' => $data['joinRequest']->id, 'status' => 'rejected']);
});


// --- Test Tribe Update Access ---
function setupTribeUpdateScenario() {
    $siteAdmin = User::factory()->create(['is_site_admin' => true]);
    $kingdomKing = User::factory()->create();
    $kingdom = Kingdom::factory()->create(['king_user_id' => $kingdomKing->id, 'is_active' => true]);
    
    $tribeLeader = User::factory()->create();
    KingdomMembership::factory()->create(['user_id' => $tribeLeader->id, 'kingdom_id' => $kingdom->id]);
    $tribeLeader->current_kingdom_id = $kingdom->id;
    $tribeLeader->save();
    
    $tribe = Tribe::factory()->create(['kingdom_id' => $kingdom->id, 'leader_user_id' => $tribeLeader->id, 'is_active' => true]);
    
    $randomUser = User::factory()->create();
    KingdomMembership::factory()->create(['user_id' => $randomUser->id, 'kingdom_id' => $kingdom->id]);
    $randomUser->current_kingdom_id = $kingdom->id;
    $randomUser->save();
    
    return compact('siteAdmin', 'kingdomKing', 'kingdom', 'tribeLeader', 'tribe', 'randomUser');
}

test('site admin can access tribe edit page and update tribe', function () {
    $data = setupTribeUpdateScenario();
    $this->actingAs($data['siteAdmin'])
        ->get(route('tribes.edit', $data['tribe']))
        ->assertOk();
    $this->actingAs($data['siteAdmin'])
        ->put(route('tribes.update', $data['tribe']), ['name' => 'New Tribe Name by Admin'])
        ->assertRedirect(); // To tribe.show or tribe.index
    $this->assertDatabaseHas('tribes', ['id' => $data['tribe']->id, 'name' => 'New Tribe Name by Admin']);
});

test('king of parent kingdom can access tribe edit page and update tribe', function () {
    $data = setupTribeUpdateScenario();
    $this->actingAs($data['kingdomKing'])
        ->get(route('tribes.edit', $data['tribe']))
        ->assertOk();
    $this->actingAs($data['kingdomKing'])
        ->put(route('tribes.update', $data['tribe']), ['name' => 'New Tribe Name by King'])
        ->assertRedirect();
    $this->assertDatabaseHas('tribes', ['id' => $data['tribe']->id, 'name' => 'New Tribe Name by King']);
});

test('leader of the tribe can access tribe edit page and update tribe', function () {
    $data = setupTribeUpdateScenario();
    $this->actingAs($data['tribeLeader'])
        ->get(route('tribes.edit', $data['tribe']))
        ->assertOk();
    $this->actingAs($data['tribeLeader'])
        ->put(route('tribes.update', $data['tribe']), ['name' => 'New Tribe Name by Leader'])
        ->assertRedirect();
    $this->assertDatabaseHas('tribes', ['id' => $data['tribe']->id, 'name' => 'New Tribe Name by Leader']);
});

test('random user cannot access tribe edit page or update tribe', function () {
    $data = setupTribeUpdateScenario();
    $this->actingAs($data['randomUser'])
        ->get(route('tribes.edit', $data['tribe']))
        ->assertForbidden();
    $this->actingAs($data['randomUser'])
        ->put(route('tribes.update', $data['tribe']), ['name' => 'Attempted Update by Random'])
        ->assertForbidden();
    $this->assertDatabaseMissing('tribes', ['id' => $data['tribe']->id, 'name' => 'Attempted Update by Random']);
});
