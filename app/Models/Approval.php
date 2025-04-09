<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo
use Illuminate\Database\Eloquent\Relations\MorphTo; // <-- Import MorphTo for polymorphic relation

// We will create/use these models
// use App\Models\User;

class Approval extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'approvals';

    /**
     * The attributes that are mass assignable.
     * Define fields that might be set when creating an approval record initially.
     * Status changes and approver details are often handled via specific methods.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approvable_id',    // Set by relation or manually
        'approvable_type',  // Set by relation or manually
        'submitter_user_id',
        'scope',
        'scope_id',
        'rejection_reason', // Might be set on update
        'approved_at',      // Usually set on update
        'approver_user_id', // Usually set on update
        // 'status' often managed via specific state methods
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
             // 'status' and 'scope' could be cast to Enums later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the parent approvable model (Saga, LonghousePost, Feast, etc.).
     * This is the core polymorphic relationship.
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who submitted the item for approval.
     */
    public function submitter(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    /**
     * Get the user who approved or rejected the item.
     */
    public function approver(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}