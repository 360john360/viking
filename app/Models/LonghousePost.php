<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\LonghouseThread;
// use App\Models\User;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;


class LonghousePost extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'longhouse_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'longhouse_thread_id',
        'user_id',
        'parent_post_id',
        'content',
    ];

    // No specific casts needed for this model based on our schema right now.
    // protected $casts = [];


    // --- Relationships ---

    /**
     * Get the thread that this post belongs to.
     */
    public function thread(): BelongsTo
    {
        // Assumes LonghouseThread model exists
        return $this->belongsTo(LonghouseThread::class, 'longhouse_thread_id');
    }

    /**
     * Get the user (author) who wrote this post.
     */
    public function user(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the parent post that this post is replying to (if any).
     */
    public function parent(): BelongsTo
    {
        // Relationship back to itself for nested replies
        return $this->belongsTo(LonghousePost::class, 'parent_post_id');
    }

    /**
     * Get the direct replies made to this post.
     */
    public function replies(): HasMany
    {
         // Relationship back to itself for nested replies
        return $this->hasMany(LonghousePost::class, 'parent_post_id');
    }


    /* // Optional Polymorphic Relationships - uncomment if individual posts need approval/visibility

    // /**
    //  * Get the post's approval record (if posts require individual approval).
    //  * (Polymorphic One-to-One)
    //  * /
    // public function approval(): MorphOne
    // {
    //     // Assumes Approval model exists
    //     return $this->morphOne(Approval::class, 'approvable');
    // }

    // /**
    //  * Get the post's visibility setting record (if posts have individual visibility).
    //  * (Polymorphic One-to-One)
    //  * /
    // public function visibilitySetting(): MorphOne
    // {
    //      // Assumes VisibilitySetting model exists
    //     return $this->morphOne(VisibilitySetting::class, 'visible');
    // }

    */
}