<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // <-- Import HasUuids trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\MorphTo;

// We will create/use these models
// use App\Models\User;

class Raven extends Model
{
    use HasFactory, HasUuids; // <-- Added HasUuids trait for UUID primary key handling

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ravens';

    /**
     * The data type of the auto-incrementing ID.
     * Overridden for UUIDs.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Overridden for UUIDs.
     *
     * @var bool
     */
    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'id' is usually not fillable as it's auto-generated (even UUIDs)
        'recipient_user_id',
        'message',
        'link_url',
        'notifiable_id',
        'notifiable_type',
        'type',
        'read_at', // Setting read_at directly might happen via update methods
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who received this notification.
     */
    public function recipient(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Get the model that the notification relates to (polymorphic).
     * (e.g., the LonghousePost, the Approval record, the User who sent a message)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}