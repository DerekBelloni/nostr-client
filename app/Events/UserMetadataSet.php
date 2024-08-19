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

    public function __construct($metadata)
    {
        $this->metadata = $metadata;
    }

    public function broadcastOn(): array
    {
        Log::info("Broadcasting metadata event");
        return [
            new Channel('user_metadata')
        ];
    }


    public function broadcastAs()
    {
        return 'metadata_set';
    }

    public function broadcastWith() {
        return ['metadata' => $this->metadata];
    }

}