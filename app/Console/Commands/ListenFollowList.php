<?php

namespace App\Console\Commands;

use App\Events\UserFollowList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenFollowList extends Command
{
    protected $signature = 'rabbitmq:follow-list';
    protected $description = 'Listen for a users follow list on RabbitMQ queue';

    private $connection;
    private $channel;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting to listen for a users follow list...");

        while (true) {
            try {
                $this->connect();
                $this->consumeMessages();
            } catch (\Exception $e) {
                $this->error("Error occurred: ", $e->getMessage());
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
        $this->channel->queue_declare('follow_list', '', false, false, false, false);
    }

    private function consumeMessages() {
        $this->info("Waiting for follow list. To exit press CTRL+C");
        $this->channel->basic_consume('follow_list', '', false, true, false, false, [$this, 'processMessage']);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $received_follows = $msg->getBody();
        $decoded_follows = json_decode($received_follows, true);
        $pubkey = $decoded_follows[2]["pubkey"];
        
        if ($received_follows) {
            $redis_key = "{$pubkey}:follows";
            $follows_set = Redis::set($redis_key, $received_follows);
        }

        if ($follows_set) {
            try {
                event(new UserFollowList(true, $pubkey));
            } catch (\Exception $e) {
                $this->error('Error firing user follows list event: ' . $e->getMessage());
            }
        } else {
            $this->warn('No follows received');
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