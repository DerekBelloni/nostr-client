<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class ListenAuthorMetadata extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:author-metadata';
    protected $description = 'Listen for searched author metadata results';

    protected function getQueueName(): string 
    {
        return 'author_metadata';
    }

    public function processMessage(AMQPMessage $msg)
    {
        Log::info('author metadata msg', [$msg->getBody()]);
    }
}