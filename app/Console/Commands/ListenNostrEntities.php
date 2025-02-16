<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class ListenNostrEntities extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:nostr-entities';
    protected $description = 'Listen for embedded nostr entities';

    protected function getQueueName(): string
    {
        return 'nostr_entities';
    }

    public function processMessage(AMQPMessage $msg)
    {
        $decoded = json_decode($msg->getBody(), true);
        Log::info("embedded nostr entity: ", [$decoded]);
    }
}