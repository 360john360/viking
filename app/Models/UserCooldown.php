<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\User;
// use App\Models\Kingdom;


class UserCooldown extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'user_cooldowns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'cooldown_type',
        'expires_at',
        'reason_kingdom_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            // 'cooldown_type' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the user associated with this cooldown record.
     */
    public function user(): BelongsTo
    {
        // Assumes User model exists
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the kingdom associated with the reason for this cooldown (if applicable).
     */
    public function reasonKingdom(): BelongsTo
    {
        // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'reason_kingdom_id');
    }
}