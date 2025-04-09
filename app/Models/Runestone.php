<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\User;
// use App\Models\Tribe;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;

class Runestone extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'runestones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'creator_user_id',
        'is_kvk_channel',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_kvk_channel' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who created this runestone announcement.
     */
    public function creator(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get the tribes targeted by this runestone (for KvK channels).
     */
    public function targetTribes(): BelongsToMany
    {
        // Assumes Tribe model exists
        // The second argument is the pivot table name.
        return $this->belongsToMany(Tribe::class, 'runestone_tribe_targets');
    }

    /**
     * Get the runestone's approval record.
     * (Polymorphic One-to-One)
     */
    public function approval(): MorphOne
    {
         // Assumes Approval model exists
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Get the runestone's visibility setting record.
     * (Polymorphic One-to-One)
     */
    public function visibilitySetting(): MorphOne
    {
          // Assumes VisibilitySetting model exists
        return $this->morphOne(VisibilitySetting::class, 'visible');
    }
}