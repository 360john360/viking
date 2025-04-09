<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\User;
// use App\Models\Kingdom;

class KingdomMembership extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set because the name isn't a standard pluralization.
     *
     * @var string
     */
    protected $table = 'kingdom_memberships';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'kingdom_id',
        'role',
        'joined_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
             // 'role' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the user associated with this membership record.
     */
    public function user(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the kingdom associated with this membership record.
     */
    public function kingdom(): BelongsTo
    {
         // Assumes Kingdom model exists
        return $this->belongsTo(Kingdom::class, 'kingdom_id');
    }
}