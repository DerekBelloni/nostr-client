<?php

namespace App\Console\Commands;

use App\Events\UserNotes;
use App\Repositories\UserNotesManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenUserNotes extends Command
{
    protected $signature = 'rabbitmq:user-notes';
    protected $description = 'Listen for user notes on RabbitMQ queue';

    private $connection;
    private $channel;
    private $noteIds = [];
    private $notes = [];
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting to listen for user notes....");

        while (true) {
            try {
                $this->connect();
                $this->consumeMessages();
            } catch (\Exception $e) {
                $this->error("Error occurred: " . $e->getMessage());
                Log::error("RabbitMQ Listener Error: " . $e->getMessage());
                $this->closeConnection();
                sleep(5);
            }
        }
    }

    private function connect() 
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('user_notes', '', false, false, false, false);
    }

    private function consumeMessages()
    {
        $this->info("Waiting for messages. To exit press CTRL+C");
        $this->channel->basic_consume('user_notes', '', false, true, false, false, [$this, 'processMessage']);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $user_notes = $msg->getBody();
        $decoded_notes = json_decode($user_notes, true);
        $pubkey = $decoded_notes[2]["pubkey"];
        $validated_notes = $this->removeDuplicateNotes($user_notes);

        if ($validated_notes) {
            $redis_key = "{$pubkey}:user-notes";
            $user_notes_set = Redis::set($redis_key, $validated_notes);
        }

        // Move this to redis controller
        // $process_user_note = new UserNotesManager();

        // if (isset($validated_note)) {
        //     $processed_note = $process_user_note->processUserNotes($validated_note);
        // }
        //
    
        if (isset($user_notes_set)) {
            try {
                event(new UserNotes(true, $pubkey));
            } catch (\Exception $e) {
                $this->error('Error firing UserNotes event: ', $e->getMessage());
            }
        } else {
            $this->warn('No user notes received');
        }
    }

    private function removeDuplicateNotes($user_note)
    {
        $decoded_note = json_decode($user_note, true);
    
        if (!in_array($decoded_note[2]["id"], $this->noteIds)) {
            array_push($this->noteIds, $decoded_note[1]);
            return $decoded_note;
        } else {
            return null;
        } 
    }

    private function closeConnection()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function __destruct()
    {
        $this->closeConnection();
    }
}