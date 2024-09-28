<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserMetadataSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct($metadata_set, $user_pubkey)
    {
        $this->metadata_set = $metadata_set;
        $this->user_pubkey = $user_pubkey;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user_metadata')
        ];
    }


    public function broadcastAs()
    {
        return 'metadata_set';
    }

    public function broadcastWith() 
    {
        return ['metadata' => $this->metadata_set, 'userPubKey' => $this->user_pubkey];
    }

}