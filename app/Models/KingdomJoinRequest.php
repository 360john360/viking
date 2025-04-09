<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Added model imports for clarity
use App\Models\User;
use App\Models\Kingdom;

class KingdomJoinRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'kingdom_join_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'kingdom_id',
        'message',
        'status', // <-- Added
        'reviewed_by_user_id', // <-- Added
        'reviewed_at', // <-- Added
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who submitted this join request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the kingdom this request is for.
     */
    public function kingdom(): BelongsTo
    {
        return $this->belongsTo(Kingdom::class, 'kingdom_id');
    }

    /**
     * Get the user (King) who reviewed this request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}