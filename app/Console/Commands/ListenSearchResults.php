<?php

namespace App\Console\Commands;

use App\Events\SearchResultsSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenSearchResults extends Command
{
    protected $signature = 'rabbitmq:search-results';
    protected $description = 'Listen for NIP-50 search results';

    private $connection;
    private $channel;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting to listen for search results...');

        while (true) {
            try {
                $this->connect();
                $this->consumeMessages();
            } catch (\Exception $e) {
                $this->error("Error occurred: " . $e->getMessage());
                Log::error("RabbitMQ Listener Error for Search: " . $e->getMessage());
                $this->closeConnection();
                sleep(5);
            }
        }
    }

    private function connect()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('search_results', false, false, false, false);
    }

    private function consumeMessages()
    {
        $this->info('Waiting for search results. To exit press CTRL+C');
        $this->channel->basic_consume('search_results', '', false, true, false, false, [$this, 'processSearchResults']);

        while($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function processSearchResults(AMQPMessage $msg) 
    {
        $received_search_results = $msg->getBody();
        $decoded_search_results = json_decode($received_search_results, true);
        $search_key = $decoded_search_results["SearchKey"];
        $pubkey = null;
        $uuid = null;

        if (ctype_xdigit($search_key)) {
            $pubkey = $search_key;
        } else {
            $uuid = $search_key;
        }

        $redis_key = "search:{$search_key}";
        $search_results_set = Redis::sAdd($redis_key, $received_search_results);

        if ($search_results_set) {
            try {
                event(new SearchResultsSet(true, $pubkey, $uuid));
            } catch (\Exception $e) {
                $this->error('Error firing Search Results Set event: ' . $e->getMessage());
            }
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