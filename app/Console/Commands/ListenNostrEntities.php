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
        $event = $decoded["Event"];
        $subscription_metadata = $decoded["SubscriptionMetadata"];
        Log::info("subscription: ", [$subscription_metadata]);
        $type = $subscription_metadata["UserContext"]["type"];
        Log::info("type: ", [$type]);

        

        // sepearate event from subscription metadata
        // stash both in redis under the uuid/pubkey as a key
    }
}