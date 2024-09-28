<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowList implements ShouldBroadcastNow 
{
    use Dispatchable, InteractsWithSockets, ShouldBroadcastNow

    public function __construct($followlist_set, $user_pubkey)
    {
        $this->followlist_set = $followlist_set;
        $this->user_pubkey = $user_pubkey;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('follow_list')
        ];
    }

    public function broadcastAs()
    {
        return 'follow_list_set';
    }

    public function broadcastWith()
    {
        return ['follow_list' => $this->followlist_set, 'userPubKey' => $this->user_pubkey]
    }
}