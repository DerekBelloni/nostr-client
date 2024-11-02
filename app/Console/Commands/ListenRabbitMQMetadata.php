<?php 

namespace App\Console\Commands;

use App\Events\UserMetadataSet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ListenRabbitMQMetadata extends Command
{
    protected $signature = 'rabbitmq:listen-metadata';
    protected $description = 'Listen for metadata messages on RabbitMQ queue';

    private $connection;
    private $channel;

    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->info("Starting to listen for metadata messages...");

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
        // Move the arguments to a config file and pull in those
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('user_metadata', false, false, false, false);
    }

    private function consumeMessages()
    {
        $this->info("Waiting for messages. To exit press CTRL+C");

        $this->channel->basic_consume('user_metadata', '', false, true, false, false, [$this, 'processMessage']);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private static function checkPubkey($pubkey) 
    {
        return Redis::exists($pubkey);
    }

    public function processMessage(AMQPMessage $msg)
    {
        $received_metadata = $msg->getBody();
        $decoded_metadata = json_decode($received_metadata, true);
        
        $pubkey = $decoded_metadata[2]["pubkey"];

        Log::info('received metadata for pubkey: ', [$pubkey]);

        $metadata_set = null;
        if ($received_metadata && self::checkPubkey($pubkey)) {
            $redis_key = "{$pubkey}:metadata";
            $metadata_set = Redis::set($redis_key, $received_metadata);
        } else {
            $redis_key = "follows_metadata";
            Redis::append($redis_key, $received_metadata);
        }
 
        // if ($metadata_set) {
        //     try {
        //         event(new UserMetadataSet(true, $pubkey));
        //     } catch (\Exception $e) {
        //         $this->error('Error firing UserMetadataSet event: ' . $e->getMessage());
        //     }
        // } else {
        //     $this->warn('No metadata received');
        // }
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