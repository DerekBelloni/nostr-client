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
        $event_id = $event[2]["id"];
        $subscription_metadata = $decoded["SubscriptionMetadata"];
        $id = $subscription_metadata["UserContext"]["ID"];
        $redis_key = "nostr_entity:{$id}";
        Log::info("embedded event: ", [$event[2]["id"]]);
        if (Redis::exists($redis_key)) {
            $existing_data = json_decode(Redis::get($redis_key), true);

            if (!array_key_exists($event_id, $existing_data)) {
                $existing_data[$event_id] = $event;
                Redis::set($redis_key, json_encode($existing_data));
                event(new NostrEntitySet(true, $id));
            } else {
                Log::info("Event not found for ID: " . $event_id);
            }
        } 
    }
}