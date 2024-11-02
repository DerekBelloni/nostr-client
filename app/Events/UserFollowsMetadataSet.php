<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UserFollowsMetadataSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($follows_metadata_set)
    {
        $this->follows_metadata_set = $follows_metadata_set;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('follows_metadata')
        ];    
    }

    public function broadcastAs()
    {
        return 'follows_metadata_set';
    }
}