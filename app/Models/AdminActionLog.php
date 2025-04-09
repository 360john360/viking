<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\MorphTo;

// We will create/use these models
// use App\Models\User;

class AdminActionLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_action_logs';

    /**
     * Indicates if the model should be timestamped.
     * We only need created_at for logs.
     * Set UPDATED_AT constant to null to disable tracking it.
     *
     * @var bool
     */
    const UPDATED_AT = null; // Disable updated_at timestamp

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_user_id',
        'action',
        'target_resource_type',
        'target_resource_id',
        'details',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array', // Cast the JSON details column to a PHP array
        ];
    }


    // --- Relationships ---

    /**
     * Get the user (admin/moderator) who performed the logged action.
     */
    public function adminUser(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Get the target resource model instance (polymorphic).
     * (e.g., the User banned, the Kingdom created, the SiteSetting changed)
     * Note: Requires target_resource_type and target_resource_id columns.
     */
    public function targetResource(): MorphTo
    {
        // Uses target_resource_type and target_resource_id columns by convention
        // (If columns were named differently, specify here: morphTo('targetResource', 'type_col', 'id_col'))
        return $this->morphTo(__FUNCTION__, 'target_resource_type', 'target_resource_id');
    }
}