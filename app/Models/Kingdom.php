<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Added for potential members() relationship later

// Conceptual Imports
use App\Models\User;
use App\Models\Tribe;
use App\Models\KingdomMembership;
use App\Models\KingdomJoinRequest;
use App\Models\KingdomRelation;


class Kingdom extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'kingdoms';

    /**
     * The attributes that are mass assignable.
     * ADDED 'is_active' to allow updating status via form.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'emblem_url',
        'is_active', // <-- ADDED
        // king_user_id is set explicitly, not usually mass assigned
    ];

    /**
     * Get the attributes that should be cast.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // --- Relationships ---

    /** Get the user who is the King of this kingdom. */
    public function king(): BelongsTo
    {
        return $this->belongsTo(User::class, 'king_user_id');
    }

    /** Get all the tribes belonging to this kingdom. */
    public function tribes(): HasMany
    {
        return $this->hasMany(Tribe::class);
    }

    /** Get all the membership records for this kingdom. */
    public function memberships(): HasMany
    {
        return $this->hasMany(KingdomMembership::class);
    }

     /** Get all the pending/processed join requests for this kingdom. */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(KingdomJoinRequest::class);
    }

    /** Get diplomatic relations where this kingdom is 'kingdom_a'. */
    public function relationsA(): HasMany
    {
        return $this->hasMany(KingdomRelation::class, 'kingdom_a_id');
    }

    /** Get diplomatic relations where this kingdom is 'kingdom_b'. */
    public function relationsB(): HasMany
    {
         return $this->hasMany(KingdomRelation::class, 'kingdom_b_id');
    }

    // --- Other relationships ---

}