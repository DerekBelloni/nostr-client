<?php

namespace App\Console\Commands;

use App\Events\UserNotes;
use App\Repositories\UserNotesManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenUserNotes extends Command
{
    protected $signature = 'rabbitmq:user-notes';
    // protected $signature = 'rabbitmq:listen-metadata';
    protected $description = 'Listen for user notes on RabbitMQ queue';

    private $connection;
    private $channel;
    private $noteIds = [];
    
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
        // $this->info("message received");
        // $user_notes = $msg->getBody();
        // $validated_note = $this->removeDuplicateNotes($user_notes);
        // $process_user_note = new UserNotesManager();

        // if (isset($validated_note)) {
        //     $processed_note = $process_user_note->processUserNotes($validated_note);
        // }
    
        // if (isset($processed_note)) {
        //     try {
        //         event(new UserNotes($processed_note));
        //         $this->info('UserNotes event fired: ' . $processed_note);
        //     } catch (\Exception $e) {
        //         $this->error('Error firing UserNotes event: ', $e->getMessage());
        //     }
        // }
        $receivedPubHexKey = $msg->getBody();
        $redis_metadata = json_decode(Redis::get($receivedPubHexKey), true);
        
        $formattedMetadata = $this->decodeMetadata($redis_metadata);

        if (isset($redis_metadata)) {
            Log::info("redis metadata set");
            try {
                event(new UserMetadataSet($formattedMetadata));
                $this->info('UserMetadataSet event fired for pubHexKey: ' . $receivedPubHexKey);
            } catch (\Exception $e) {
                $this->error('Error firing UserMetadataSet event: ' . $e->getMessage());
            }
        } else {
            $this->warn('No metadata found in Redis for pubHexKey: ' . $receivedPubHexKey);
        }
    }

    private function removeDuplicateNotes($user_note)
    {
        $decoded_note = json_decode($user_note, true);
    
        if (!in_array($decoded_note[1], $this->noteIds)) {
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