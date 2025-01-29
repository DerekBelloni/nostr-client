<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;

class AuthorMetadataSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($author_metadata_set, $user_pubkey, $uuid)
    {
        $this->author_metadata_set = $author_metadata_set;
        $this->user_pubkey = $user_pubkey;
        $this->uuid = $uuid;
    }

    public function broadcastOn()
    {
        return [
            new Channel('author_metadata')
        ];
    }

    public function broadcastAs()
    {
        return 'author_metadata_set';
    } 

    public function broadcastWith()
    {
        return ['author_metadata_set' => $this->author_metadata_set, 'user_pubkey' => $this->user_pubkey, 'uuid' => $this->uuid];
    }
}
