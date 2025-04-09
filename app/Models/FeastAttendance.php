<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\Feast;
// use App\Models\User;

class FeastAttendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set for clarity.
     *
     * @var string
     */
    protected $table = 'feast_attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'feast_id',
        'user_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
             // 'status' could be cast to an Enum later if desired
        ];
    }


    // --- Relationships ---

    /**
     * Get the feast (event) associated with this attendance record.
     */
    public function feast(): BelongsTo
    {
        // Assumes Feast model exists
        return $this->belongsTo(Feast::class, 'feast_id');
    }

    /**
     * Get the user associated with this attendance record.
     */
    public function user(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'user_id');
    }
}