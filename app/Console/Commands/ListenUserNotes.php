<?php

namespace App\Console\Commands;

use App\Events\UserNotes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenUserNotes extends Command
{
    protected $signature = 'rabbitmq:user-notes';
    protected $description = 'Listen for user notes on RabbitMQ queue';

    private $connection;
    private $channel;

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
        $processed_notes = self::removeDuplicateNotes($user_notes);

        if (isset($processed_notes)) {
            try {
                event(new UserNotes($processed_notes));
                $this->info('UserNotes event fired: ' . $processed_notes);
            } catch (\Exception $e) {
                $this->error('Error firing UserNotes event: ', $e->getMessage());
            }
        }
    }

    private function removeDuplicateNotes($user_notes)
    {
        // decode json
        $decoded_notes = json_decode($user_notes, true);
        Log::info("decoded notes: ", $decoded_notes);
        // unset the duplicates - eventually this should happen in go before
        $eventIds = [];
        foreach ($decoded_notes as $index => $event) {
            if ($event[0] == 'EVENT') {
                Log::info("array key 1: ", $event[1]);
                if (!in_array($event[1], $eventIds)) {
                    array_push($eventIds, $event[1]);
                } else {
                    unset($decoded_notes[$index]);
                }
            }
        }

        return $decoded_notes;
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
        
    }
}