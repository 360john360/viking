<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import BelongsTo

// We will create/use these models
// use App\Models\User;

class SiteSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_settings';

    /**
     * The primary key associated with the table.
     * Using 'key' allows finding settings by their name easily.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Set to false because 'key' is not auto-incrementing.
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
        'key',
        'value',
        'description',
        'last_updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Casting 'value' depends on how you store/retrieve it (e.g., JSON, boolean as 0/1)
            // Add casts here later if needed based on specific setting types.
        ];
    }


    // --- Relationships ---

    /**
     * Get the user (admin) who last updated this setting.
     */
    public function lastUpdatedBy(): BelongsTo
    {
         // Assumes User model exists
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}