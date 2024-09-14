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
    // protected $signature = 'test';
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
        $this->connection = new AMQPStreamConnection('localhost', 25672, 'guest', 'guest', '/');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('metadata_set', false, false, false, false);
    }

    private function consumeMessages()
    {
        $this->info("Waiting for messages. To exit press CTRL+C");

        $this->channel->basic_consume('metadata_set', '', false, true, false, false, [$this, 'processMessage']);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function processMessage(AMQPMessage $msg)
    {
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

    private function decodeMetadata($metadata)
    {
        if (isset($metadata[2]["content"])) {
            $metadata[2]["content"] = json_decode($metadata[2]["content"], true);
        }
        return $metadata[2] ?? null;
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