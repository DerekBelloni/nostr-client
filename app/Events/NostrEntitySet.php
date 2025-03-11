<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;

class NostrEntitySet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($nostr_entity_set, $entity_key)
    {
        $this->nostr_entity_set = $nostr_entity_set;
        $this->entity_key = $entity_key;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('nostr_entity')
        ];
    }

    public function broadcastAs()
    {
        return 'nostr_entity_set';
    }

    public function broadcastWith()
    {
        return ['nostr_entity_set' => $this->nostr_entity_set, 'entitiy_key' => $this->entity_key];
    }
}