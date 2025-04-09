<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo
use Illuminate\Database\Eloquent\Relations\MorphTo;   // <-- Import MorphTo

// We will create/use these models
// use App\Models\User;

class CouncilJudgement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'council_judgements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'actionable_id',
        'actionable_type',
        'moderator_user_id',
        'action_type',
        'reason',
        'scope',
        'scope_id',
        'expires_at',
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
            // 'action_type', 'scope' could be cast to Enums later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the parent actionable model (User, LonghousePost, Saga, etc.).
     * This is the core polymorphic relationship.
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user (moderator) who issued this judgement.
     */
    public function moderator(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'moderator_user_id');
    }
}