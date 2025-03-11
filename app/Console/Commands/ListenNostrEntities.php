<?php

namespace App\Console\Commands;

use App\Events\NostrEntitySet;
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
        Log::info("subscription meta upgrade: ", [$subscription_metadata["EventID"]]);
        $type = $subscription_metadata["UserContext"]["Type"];
        $id = $subscription_metadata["UserContext"]["ID"];
        $redis_key = "nostr_entity:{$id}";

        Log::info("apple", [$event[1]]);

        if (Redis::exists($redis_key)) {
            $existing_event = json_decode(Redis::get($redis_key), true);
            // Log::info('existing event: ', [$existing_event[0]]);$event_id = $subscription_metadata["EventID"];
            $event_id = $subscription_metadata["EventID"];
            if (isset($existing_event[$event_id])) { // Flat array, direct key access
                $existing_event[$event_id] = $event;
                Redis::set($redis_key, json_encode($existing_event));
                event(new NostrEntitySet(true, $id));
            } else {
                Log::info("Event not found for ID: " . $event_id);
            }
        } 

        

        

        // sepearate event from subscription metadata
        // stash both in redis under the uuid/pubkey as a key
    }
}