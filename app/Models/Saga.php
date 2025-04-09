<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import relation types
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// We will create/use these models
// use App\Models\User;
// use App\Models\SagaRevision;
// use App\Models\Approval;
// use App\Models\VisibilitySetting;

class Saga extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'sagas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'creator_user_id', // Usually set explicitly, but can be fillable
        'is_locked',
        // current_revision_id is typically managed via relationship updates
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
        ];
    }


    // --- Relationships ---

    /**
     * Get the user who originally created the saga.
     */
    public function creator(): BelongsTo
    {
        // Assumes User model exists
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get all revisions associated with this saga.
     */
    public function revisions(): HasMany
    {
        // Assumes SagaRevision model exists or will exist
        return $this->hasMany(SagaRevision::class);
    }

    /**
     * Get the specific revision that is currently active for this saga.
     */
    public function currentRevision(): BelongsTo
    {
         // Assumes SagaRevision model exists or will exist
        return $this->belongsTo(SagaRevision::class, 'current_revision_id');
    }

    /**
     * Get the saga's approval record.
     * (Polymorphic One-to-One)
     */
    public function approval(): MorphOne
    {
        // Assumes Approval model exists
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Get the saga's visibility setting record.
     * (Polymorphic One-to-One)
     */
    public function visibilitySetting(): MorphOne
    {
         // Assumes VisibilitySetting model exists
        return $this->morphOne(VisibilitySetting::class, 'visible');
    }
}