<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserNotes implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct($user_notes_set, $user_pubkey, $receiving_users_pubkey = null)
    {
        $this->user_notes_set = $user_notes_set;
        $this->user_pubkey = $user_pubkey;
        $this->receiving_users_pubkey = $receiving_users_pubkey;
        Log::info("in event, receiving pubkey: ", [$this->receiving_users_pubkey]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user_notes')
        ];
    }

    public function broadcastAs()
    {
        return 'user_notes_set';
    }

    public function broadcastWith() 
    {
        return ['receiving_users_pubkey' => $this->receiving_users_pubkey, 'usernotes' => $this->user_notes_set, 'userPubKey' => $this->user_pubkey];
    }
}