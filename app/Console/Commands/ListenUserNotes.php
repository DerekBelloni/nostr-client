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
        $decoded_note = json_decode($user_notes, true);

        if (!isset($decoded_note[2]["pubkey"]) || !isset($decoded_note[2]["id"])) {
            $this->error("Invalid note structure");
            return;
        }

        $pubkey = $decoded_note[2]["pubkey"];
        $note_id = $decoded_note[2]["id"];
        $redis_key = "{$pubkey}:user-notes";

        if (!self::noteExists($redis_key, $note_id)) {
            $encoded_note = json_encode($decoded_note);

            Redis::rpush($redis_key, $encoded_note);
            $this->info("Added new note {$note_id} for user {$pubkey}");
            $notes = Redis::lrange($redis_key, 0, -1);
            Log::info('notes from redis',[$notes]);
        }
    
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

    private function noteExists($redis_key, $note_id)
    {
        $notes = Redis::lrange($redis_key, 0, -1);
        if (!$notes) {
            return false;
        }

        foreach ($notes as $note) {
            $decoded_note = json_decode($note, true);
            if ($decoded_note[2]['id'] && $decoded_note[2]['id'] === $note_id) {
                Log::info("decoded note id: ", [$decoded_note[2]['id']]);
                Log::info("note id: ", [$note_id]);
                return true;
            }
        }
        return false;
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