<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// We will create/use these models
// use App\Models\UserAchievement;
// use App\Models\User;

class Achievement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'achievements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'icon_url',
        'type',
        'is_repeatable',
        'criteria_description',
        'internal_trigger_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_repeatable' => 'boolean',
            // 'type' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get all the records showing users who earned this achievement.
     */
    public function userAchievements(): HasMany
    {
        // Assumes UserAchievement model exists or will exist
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Get all the users who have earned this achievement.
     * (Many-to-Many relationship through user_achievements table)
     */
    public function users(): BelongsToMany
    {
        // Assumes User model exists
        // The second argument is the pivot table name.
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('earned_at', 'awarded_by_user_id', 'related_context') // Load extra pivot data
                    ->withTimestamps(); // Load pivot created_at/updated_at
    }
}