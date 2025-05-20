<?php

namespace App\Events;

use App\Models\KingClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KingClaimSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public KingClaim $kingClaim;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\KingClaim $kingClaim
     */
    public function __construct(KingClaim $kingClaim)
    {
        $this->kingClaim = $kingClaim;
    }
}
