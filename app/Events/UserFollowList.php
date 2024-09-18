<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;

class UserFollowList implements ShouldBroadcastNow 
{
    use Dispatchable, InteractsWithSockets, ShouldBroadcastNow

    public function __construct()
    {
        
    }
}