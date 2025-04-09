<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\Longhouse;
// use App\Models\User;
// use App\Models\LonghousePost;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;


class LonghouseThread extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'longhouse_threads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'longhouse_id',
        'title',
        'creator_user_id',
        'is_pinned',
        'is_locked',
        // last_reply_at is usually updated automatically by application logic/events
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'last_reply_at' => 'datetime',
        ];
    }


    // --- Relationships ---

    /**
     * Get the longhouse category that this thread belongs to.
     */
    public function longhouse(): BelongsTo
    {
        // Assumes Longhouse model exists
        return $this->belongsTo(Longhouse::class, 'longhouse_id');
    }

    /**
     * Get the user who created this thread.
     */
    public function creator(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get all the posts/replies within this thread.
     */
    public function posts(): HasMany
    {
        // Assumes LonghousePost model exists or will exist
        return $this->hasMany(LonghousePost::class);
    }

    /**
     * Get the thread's approval record (if threads require approval).
     * (Polymorphic One-to-One)
     */
    public function approval(): MorphOne
    {
         // Assumes Approval model exists
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Get the thread's visibility setting record.
     * (Polymorphic One-to-One)
     */
    public function visibilitySetting(): MorphOne
    {
          // Assumes VisibilitySetting model exists
        return $this->morphOne(VisibilitySetting::class, 'visible');
    }
}