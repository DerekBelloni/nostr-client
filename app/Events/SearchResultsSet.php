<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SearchResultsSet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct($search_results_set)
    {
        
    }

    public function broadcastOn()
    {
        
    }

    public function broadcastAs()
    {

    }

    public function broadcastWith()
    {
        
    }
}