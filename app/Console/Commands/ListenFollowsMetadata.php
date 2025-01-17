<?php

namespace App\Console\Commands;

use App\Events\FollowsMetadataSet;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;

class ListenFollowsMetadata extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:follows-metadata';
    protected $description = 'Listen for follows metadata';

    protected function getQueueName(): string
    {
        return 'follows_metadata';
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $received_follows_metadata = $msg->getBody();
        $decoded_follows_metadata = json_decode($received_follows_metadata, true);
        $pubkey = $decoded_follows_metadata[2]["pubkey"];
        FacadesLog::info('follows metadata pubkey: ', [$pubkey]);

        $redis_key = "follows_metadata";
        if (!self::checkExistingFollows($redis_key, $pubkey)) {
            $follows_set = Redis::sAdd($redis_key, $received_follows_metadata);
        } else {
            $follows_set = true;
            $this->info("Follows already set");
        }
        
        if ($follows_set) {
            try {
                event(new FollowsMetadataSet(true));
                $this->info("UserFollowsMetadataSet event fired");
                $this->channel->basic_ack($msg->getDeliveryTag());
            } catch (\Exception $e) {
                $this->error('Error firing UserFollowsMetadataSet event: ' . $e->getMessage());
                $this->channel->basic_nack($msg->getDeliveryTag(), false, false);
            }
        } else {
            $this->warn('No follows metadata received');
            $this->channel->basic_nack($msg->getDeliveryTag(), false, false);
        }
    }

    private static function checkExistingFollows($redis_key, $pubkey)
    {
        $existing_follows_metadata = Redis::sMembers($redis_key);
        $key_exists = false;

        if ($existing_follows_metadata === false || !is_array($existing_follows_metadata)) {
            return false;
        }

        foreach($existing_follows_metadata as $metadata) {
            $decoded_metadata = json_decode($metadata, true);
            $metadata_pubkey = $decoded_metadata[2]["pubkey"];
            $key_exists = $pubkey === $metadata_pubkey;
        }

        return $key_exists;
    }
}

