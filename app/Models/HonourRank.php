<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Import HasMany

// We will create/use these models
// use App\Models\User;

class HonourRank extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'honour_ranks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'level',
        'min_days_in_kingdom',
        'min_saga_contributions',
        'min_feasts_attended',
        'description',
        'permissions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'min_days_in_kingdom' => 'integer',
            'min_saga_contributions' => 'integer',
            'min_feasts_attended' => 'integer',
            'permissions' => 'array', // Cast the JSON column to a PHP array automatically
        ];
    }

    // --- Relationships ---

    /**
     * Get all the users who currently hold this honour rank.
     */
    public function users(): HasMany
    {
         // Assumes User model exists
        return $this->hasMany(User::class, 'honour_rank_id');
    }
}