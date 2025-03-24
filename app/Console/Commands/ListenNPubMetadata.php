<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class ListenNPubMetadata extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:npub-metadata';
    protected $description = 'Listen for npub metadata on RabbitMQ queue';

    protected function getQueueName(): string
    {
        return 'npub_result';
    }

    public function processMessage(AMQPMessage $msg)
    {
        $received_npub_metadata = $msg->getBody();
        $decoded_npub_metadata = json_decode($received_npub_metadata, true);
        Log::info("decded npub metadata: ", [$decoded_npub_metadata]);
    }
}