<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\User;
// use App\Models\LonghouseThread;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;

class Longhouse extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'longhouses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'creator_user_id', // Optional: Allow setting creator on mass assignment
    ];

    // No specific casts needed for this model based on our schema right now.
    // protected $casts = [];


    // --- Relationships ---

    /**
     * Get the user who originally created the longhouse category.
     */
    public function creator(): BelongsTo
    {
        // Assumes User model exists
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get all the threads within this longhouse category.
     */
    public function threads(): HasMany
    {
        // Assumes LonghouseThread model exists or will exist
        return $this->hasMany(LonghouseThread::class);
    }

    /**
     * Get the longhouse's approval record (if categories require approval).
     * (Polymorphic One-to-One)
     */
    public function approval(): MorphOne
    {
        // Assumes Approval model exists
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Get the longhouse's visibility setting record.
     * (Polymorphic One-to-One)
     */
    public function visibilitySetting(): MorphOne
    {
         // Assumes VisibilitySetting model exists
        return $this->morphOne(VisibilitySetting::class, 'visible');
    }
}