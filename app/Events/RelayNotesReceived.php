<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Redis;

class RelayNotesReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $notes;

    public function __construct(array $notes)
    {
        $this->notes = $notes;
    }

    public function broadcastOn(): Channel
    {
        $encoded_notes = json_encode($this->notes);
        Redis::set('damus-notes', $encoded_notes);
        
        $stored_value = Redis::get('damus-notes');
        return new Channel('relay-notifications');
    }
}