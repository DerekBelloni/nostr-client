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

    private $notes;
    public $isSet;

    public function __construct(array $notes)
    {
        $this->notes = $notes;
        $this->isSet = false;
    }

    public function broadcastOn(): Channel
    {
        $encoded_notes = json_encode($this->notes);
        dd($encoded_notes);
        Redis::set('damus-notes', $encoded_notes);
        $this->isSet = true;
        
        return new Channel('relay-notifications');
    }
}