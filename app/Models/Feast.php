<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\User;
// use App\Models\LonghouseThread;
// use App\Models\FeastAttendance;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;

class Feast extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'feasts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'event_type',
        'start_time',
        'end_time',
        'location',
        'creator_user_id',
        'is_recurring',
        'recurrence_rule',
        'associated_longhouse_thread_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_recurring' => 'boolean',
            // 'event_type' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who created this feast/event.
     */
    public function creator(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get the associated discussion thread for this feast/event (if any).
     */
    public function discussionThread(): BelongsTo
    {
         // Assumes LonghouseThread model exists
        return $this->belongsTo(LonghouseThread::class, 'associated_longhouse_thread_id');
    }

    /**
     * Get all the attendance records (including RSVP status) for this feast.
     */
    public function attendances(): HasMany
    {
        // Assumes FeastAttendance model exists or will exist
        return $this->hasMany(FeastAttendance::class);
    }

    /**
     * Get all the users attending this feast (many-to-many relationship through feast_attendances).
     */
    public function attendees(): BelongsToMany
    {
        // Assumes User model exists and FeastAttendance model exists (for pivot table info)
        // The second argument is the pivot table name.
        return $this->belongsToMany(User::class, 'feast_attendances')
                    ->withPivot('status') // Optionally load the status from the pivot table
                    ->withTimestamps();   // Optionally load timestamps from the pivot table
    }


    /**
     * Get the feast's approval record.
     * (Polymorphic One-to-One)
     */
    public function approval(): MorphOne
    {
         // Assumes Approval model exists
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Get the feast's visibility setting record.
     * (Polymorphic One-to-One)
     */
    public function visibilitySetting(): MorphOne
    {
         // Assumes VisibilitySetting model exists
        return $this->morphOne(VisibilitySetting::class, 'visible');
    }
}