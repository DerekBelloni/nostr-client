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
        $redis_lookup_key = "nostr_entity:{$id}";

        if (!Redis::exists($redis_lookup_key)) {
            Log::info("UUID key not found: [$redis_lookup_key]");
            return;
        }

        Log::info("nostr entity event: ", $event);
        $redis_final_key = "nostr_entity:{$id}:{$event_id}";
        
        if (!Redis::exists($redis_final_key)) {
            Redis::set($redis_final_key, json_encode($event[2]));
            Redis::del($redis_lookup_key);
            event(new NostrEntitySet(true, $id, $event_id));
        } else {
            Log::info("Event already exists at: [$redis_final_key]");
        }
    }
}