<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
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
        $type = $subscription_metadata["UserContext"]["Type"];
        $id = $subscription_metadata["UserContext"]["ID"];
        $redis_key = "nostr_entity:{$id}";

        if (Redis::exists($redis_key)) {
            $existing_event = json_decode(Redis::get($redis_key), true);
            Log::info('existing event: ', [$existing_event]);
            if ($existing_event[$event['id']]) {
                Log::info("banana", [$event]);
            }
        } 

        

        

        // sepearate event from subscription metadata
        // stash both in redis under the uuid/pubkey as a key
    }
}