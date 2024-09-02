<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotes implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct($user_notes)
    {
        $this->user_notes = $user_notes;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user_notes')
        ];
    }

    public function broadcastAs()
    {
        return 'user_notes_retrieved';
    }

    public function broadcastWith() 
    {
        return ['usernotes' => $this->user_notes];
    }
}