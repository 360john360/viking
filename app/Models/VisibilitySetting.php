<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo
use Illuminate\Database\Eloquent\Relations\MorphTo; // <-- Import MorphTo

// We will create/use these models
// use App\Models\Tribe;
// use App\Models\Kingdom;


class VisibilitySetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'visibility_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'visible_id',       // Set by relation or manually
        'visible_type',     // Set by relation or manually
        'level',
        'owning_tribe_id',
        'owning_kingdom_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'level' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the parent visible model (Saga, LonghousePost, Feast, etc.).
     * This is the core polymorphic relationship.
     */
    public function visible(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tribe that owns/scopes this visibility setting (if applicable).
     */
    public function owningTribe(): BelongsTo
    {
        // Assumes Tribe model exists
        return $this->belongsTo(Tribe::class, 'owning_tribe_id');
    }

    /**
     * Get the kingdom that owns/scopes this visibility setting (if applicable).
     */
    public function owningKingdom(): BelongsTo
    {
        // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'owning_kingdom_id');
    }
}