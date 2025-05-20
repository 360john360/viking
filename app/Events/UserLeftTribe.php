<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeftTribe
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public int $leftTribeId;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user
     * @param int $leftTribeId
     */
    public function __construct(User $user, int $leftTribeId)
    {
        $this->user = $user;
        $this->leftTribeId = $leftTribeId;
    }
}
