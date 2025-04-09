<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\User;
// use App\Models\Achievement;

class UserAchievement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'user_achievements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'achievement_id',
        'earned_at',
        'awarded_by_user_id', // Null if awarded automatically
        'related_context',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'related_context' => 'array', // Cast the JSON context column to a PHP array
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who earned this achievement instance.
     */
    public function user(): BelongsTo
    {
        // Assumes User model exists
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the achievement definition related to this instance.
     */
    public function achievement(): BelongsTo
    {
         // Assumes Achievement model exists
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    /**
     * Get the user (admin/mod) who manually awarded this achievement (if applicable).
     */
    public function awarder(): BelongsTo
    {
        // Assumes User model exists
        return $this->belongsTo(User::class, 'awarded_by_user_id');
    }
}