<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- Added

// Conceptual Imports
use App\Models\Kingdom;
use App\Models\User;
use App\Models\TribeMembership;

class Tribe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tribes';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'emblem_url',
        'kingdom_id',
        'is_active',
        'leader_user_id', // Allow admin/king to set this
    ];

     protected function casts(): array
    {
         return [
             'is_active' => 'boolean',
         ];
    }

    // --- Relationships ---
    public function kingdom(): BelongsTo {
        return $this->belongsTo(Kingdom::class, 'kingdom_id');
    }
    /** Get the designated single Tribe Leader (Thane/Chief/R5) */
    public function leader(): BelongsTo {
        return $this->belongsTo(User::class, 'leader_user_id');
    }
    /** Get all membership records for this tribe. */
    public function memberships(): HasMany {
        return $this->hasMany(TribeMembership::class);
    }

    /** Get all users who are members (any role) */
    public function members(): BelongsToMany {
         return $this->belongsToMany(User::class, 'tribe_memberships')
                     ->withPivot('role', 'joined_at') // Load role/joined data from pivot
                     ->withTimestamps(); // Load pivot timestamps
    }

    /** Get users who are specifically Officers (R4) */
    public function officers(): BelongsToMany { // <-- ADDED METHOD
         return $this->belongsToMany(User::class, 'tribe_memberships')
                     ->wherePivot('role', 'officer') // Filter pivot table
                     ->withPivot('joined_at')
                     ->withTimestamps();
    }
    // ... other relationships ...
}