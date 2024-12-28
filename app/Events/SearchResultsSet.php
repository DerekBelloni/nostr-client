<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;

class SearchResultsSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($search_results_set, $user_pubkey, $uuid)
    {
        $this->search_results_set = $search_results_set;
        $this->uuid = $uuid;
        $this->user_pubkey = $user_pubkey;
    }

    public function broadcastOn()
    {
        return [
            new Channel('search_results')
        ];
    }

    public function broadcastAs()
    {
        return 'search_results_set';
    }

    public function broadcastWith()
    {
        return ['search_results_set' => $this->search_results_set, 'user_pubkey' => $this->user_pubkey, 'uuid' => $this->uuid];
    }
}