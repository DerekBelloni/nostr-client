<?php

namespace App\Console\Commands;

use App\Events\UserNotes;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;

class ListenUserNotes extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:user-notes';
    protected $description = 'Listen for user notes on RabbitMQ queue';

    protected function getQueueName(): string
    {
        return 'user_notes';
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $user_notes = $msg->getBody();
        $decoded_note = json_decode($user_notes, true);
        $event_type = null;
        $receiving_user_pubkey = null;
        $this->channel->basic_ack($msg->getDeliveryTag());

        if (is_array($decoded_note) && array_key_exists('Event', $decoded_note)) {
            $event_type = key($decoded_note['Event']);
        
            if ($event_type === "follows") {
                $receiving_user_pubkey = trim($decoded_note["UserPubkey"], '"');
                $decoded_note = $decoded_note['Event']['follows'];
            }
        }

        
        if (!isset($decoded_note[2]["pubkey"]) || !isset($decoded_note[2]["id"])) {
            $this->error("Invalid note structure");
            return;
        }

        $pubkey = $decoded_note[2]["pubkey"];
        $note_id = $decoded_note[2]["id"];
        $redis_key = is_null($event_type) ? "{$pubkey}:user-notes" : "{$pubkey}:follow-notes";

        $note_added = false;
        $note_exists = false;
        $existing_notes = Redis::sMembers($redis_key);
      
        foreach($existing_notes as $note) {
            $existing_note_id = json_decode($note, true)[2]['id'];
            if ($existing_note_id == $note_id) {
                $note_exists = true;
                break;
            }
        }

        if (!$note_exists) {
            $encoded_note = json_encode($decoded_note);
            $note_added = Redis::sAdd($redis_key, $encoded_note);
            $this->info("Added new note {$note_id} for user {$pubkey}");
        } else {
            $this->info("Note {$note_id} already exists for user {$pubkey}");
        }

        if ($event_type === 'follows') {
            try {
                event(new UserNotes(true, $pubkey, $receiving_user_pubkey));
                $note_added = false;
            } catch (\Exception $e) {
                $this->error('Error firing UserNotes event for Follows: ', $e->getMessage());
            }
        } else {
            $this->warn('No follows notes received');
        }
    
        if ($note_added || $note_exists) {
            try {
                event(new UserNotes(true, $pubkey, null));
                $note_added = false;
            } catch (\Exception $e) {
                $this->error('Error firing UserNotes event: ', $e->getMessage());
            }
        } else {
            $this->warn('No user notes received');
        }
    }
}