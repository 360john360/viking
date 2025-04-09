<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model Imports
use App\Models\User;
use App\Models\Tribe;

class TribeJoinRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tribe_join_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'tribe_id',
        'message',
        'status', // Allow updating status
        'reviewed_by_user_id', // Allow setting reviewer
        'reviewed_at', // Allow setting review time
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
        // 'status' could be cast to an Enum later
    ];

    // --- Relationships ---

    /**
     * Get the user who submitted this join request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the tribe this request is for.
     */
    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class, 'tribe_id');
    }

    /**
     * Get the user (Leader/Officer) who reviewed this request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}