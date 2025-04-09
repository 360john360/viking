<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\Saga;
// use App\Models\User;

class SagaRevision extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'saga_revisions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'saga_id',
        'editor_user_id',
        'content',
        'edit_summary',
        'revision_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
        ];
    }


    // --- Relationships ---

    /**
     * Get the saga that this revision belongs to.
     */
    public function saga(): BelongsTo
    {
        // Assumes Saga model exists
        return $this->belongsTo(Saga::class, 'saga_id');
    }

    /**
     * Get the user who edited and saved this revision.
     */
    public function editor(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'editor_user_id');
    }
}