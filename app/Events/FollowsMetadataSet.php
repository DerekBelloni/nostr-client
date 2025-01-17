<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;

class FollowsMetadataSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($follows_metadata_set)
    {
        $this->follows_metadata_set = $follows_metadata_set;
    }

    public function broadcastOn()
    {
        return [
            new Channel('follows_metadata')
        ];
    }

    public function broadcastAs() 
    {
        return 'follows_metadata_set';
    }

    public function broadcastWith()
    {
        return ['follows_metadata_set' => $follows_metadata_set];
    }
}