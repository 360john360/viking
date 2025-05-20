<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeftKingdom
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public int $leftKingdomId;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user
     * @param int $leftKingdomId
     */
    public function __construct(User $user, int $leftKingdomId)
    {
        $this->user = $user;
        $this->leftKingdomId = $leftKingdomId;
    }
}
