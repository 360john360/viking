<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model Imports
use App\Models\Kingdom;
use App\Models\Tribe; // <-- Ensure Tribe is imported
use App\Models\HonourRank;
use App\Models\KingdomMembership;
use App\Models\TribeMembership;
use App\Models\KingdomJoinRequest;
use App\Models\TribeJoinRequest; // <-- Ensure TribeJoinRequest is imported
use App\Models\UserCooldown;

class User extends Authenticatable // implements MustVerifyEmail (if needed later)
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_kingdom_id',
        'current_tribe_id',
        'honour_rank_id',
    ];

    protected $hidden = [ 'password', 'remember_token', ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime', // Assuming you might add this later
        'is_site_admin' => 'boolean',
        'is_site_moderator' => 'boolean',
        'is_king_candidate_verified' => 'boolean',
    ];

    // --- Relationships ---
    public function currentKingdom(): BelongsTo { return $this->belongsTo(Kingdom::class, 'current_kingdom_id'); }
    public function currentTribe(): BelongsTo { return $this->belongsTo(Tribe::class, 'current_tribe_id'); }
    public function honourRank(): BelongsTo { return $this->belongsTo(HonourRank::class, 'honour_rank_id'); }
    public function kingdomMembership(): HasOne { return $this->hasOne(KingdomMembership::class); }
    public function tribeMembership(): HasOne { return $this->hasOne(TribeMembership::class); }
    public function kingdomJoinRequests(): HasMany { return $this->hasMany(KingdomJoinRequest::class, 'user_id'); }
    public function tribeJoinRequests(): HasMany { return $this->hasMany(TribeJoinRequest::class, 'user_id'); }
    public function cooldowns(): HasMany { return $this->hasMany(UserCooldown::class, 'user_id'); }
    public function kingdomsAsKing(): HasMany { return $this->hasMany(Kingdom::class, 'king_user_id'); }

    /** Get tribes led BY this user */
    public function tribesAsLeader(): HasMany // <-- Added Relationship
    {
        return $this->hasMany(Tribe::class, 'leader_user_id');
    }
    // --- Other relationships to add later ---


    // --- Helper Methods ---
    public function isSiteAdmin(): bool { return $this->is_site_admin ?? false; }
    public function isKing(): bool { return $this->kingdomsAsKing()->where('is_active', true)->exists(); } // Added is_active check here too

    // --- ADDED/Corrected Tribe Role Helpers ---
    public function isTribeLeader(Tribe $tribe): bool {
        // Check the tribe record directly
        return $tribe->leader_user_id === $this->id;
    }
    public function isTribeOfficer(Tribe $tribe): bool {
        // Check role in the membership pivot table for THIS specific tribe
        // Requires the membership relation to be loaded or queried
        // return $this->tribeMembership()->where('tribe_id', $tribe->id)->where('role','officer')->exists(); // Query approach
         return $this->tribeMembership?->tribe_id === $tribe->id && $this->tribeMembership?->role === 'officer'; // Direct check if membership loaded
    }
    public function isTribeOfficerOrLeader(Tribe $tribe): bool {
        return $this->isTribeLeader($tribe) || $this->isTribeOfficer($tribe);
    }
    // ADDED: Helper check if user leads ANY active tribe
    public function isThaneOfAnyTribe(): bool // <-- Added Method
    {
        // Check if there's any ACTIVE tribe where this user is the leader
        return $this->tribesAsLeader()->where('is_active', true)->exists(); // Added is_active check
    }
    // --- END ADDED/Corrected ---

    public function hasPendingTribeRequest(Tribe $tribe): bool {
         return $this->tribeJoinRequests()->where('tribe_id', $tribe->id)->where('status', 'pending')->exists(); // Use relationship
    }

    // --- New RBAC Helper Methods ---
    public function isSiteModerator(): bool
    {
        return $this->is_site_moderator ?? false;
    }

    public function isKingdomModerator(Kingdom $kingdom): bool
    {
        if (!$this->kingdomMembership) { // Assumes kingdomMembership is HasOne
            return false;
        }
        return $this->kingdomMembership->kingdom_id === $kingdom->id && $this->kingdomMembership->role === 'moderator';
    }

    public function isMemberOfKingdom(Kingdom $kingdom): bool
    {
        return $this->current_kingdom_id === $kingdom->id;
    }

    public function isMemberOfTribe(Tribe $tribe): bool
    {
        return $this->current_tribe_id === $tribe->id;
    }
}