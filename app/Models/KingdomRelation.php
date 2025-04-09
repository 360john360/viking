<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\Kingdom;
// use App\Models\User;

class KingdomRelation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'kingdom_relations';

    /**
     * The attributes that are mass assignable.
     * These might be set when initiating a diplomatic proposal.
     * Status changes and approvals are often handled by specific methods.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kingdom_a_id',
        'kingdom_b_id',
        'initiated_by_kingdom_id',
        // 'status', 'approved_by_a_king_id', 'approved_by_b_king_id', 'established_at' handled by logic
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'established_at' => 'datetime',
            // 'status' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the first kingdom in the relation (conventionally the one with lower ID).
     */
    public function kingdomA(): BelongsTo
    {
        // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'kingdom_a_id');
    }

    /**
     * Get the second kingdom in the relation (conventionally the one with higher ID).
     */
    public function kingdomB(): BelongsTo
    {
        // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'kingdom_b_id');
    }

    /**
     * Get the kingdom that initiated this diplomatic status or proposal.
     */
    public function initiatorKingdom(): BelongsTo
    {
        // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'initiated_by_kingdom_id');
    }

    /**
     * Get the user (King of A) who approved this status change.
     */
    public function approverA(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'approved_by_a_king_id');
    }

     /**
     * Get the user (King of B) who approved this status change.
     */
    public function approverB(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'approved_by_b_king_id');
    }
}