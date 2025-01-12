<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class BaseRabbitMQListener extends Command
{
    protected $connection;
    protected $channel;

    abstract protected function getQueueName(): string;
    abstract protected function processMessage(AMQPMessage $msg);

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting to listen for {$this->signature} messages...");

        while (true) {
            try {
                $this->connect();
                $this->consumeMessages();
            } catch (\Exception $e) {
                $this->error("Error occurred: " . $e->getMessage());
                Log::error("RabbitMQ Listener Error for {$this->signature}: " . $e->getMessage());
                $this->closeConnection();
                sleep(5);
            }
        }
    }

    protected function connect()
    {
        // Move the arguments to a config file and pull in those
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->getQueueName(), false, false, false, true, false);
    }

    protected function consumeMessages()
    {
        $this->info("Waiting for messages. To exit press CTRL+C");

        $this->channel->basic_consume($this->getQueueName(), '', false, false, false, false, [$this, 'processMessage']);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    protected function closeConnection()
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