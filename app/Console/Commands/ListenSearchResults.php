<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        Log::info(json_decode($received_search_results, true));
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