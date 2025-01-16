<?php

namespace App\Console\Commands;

use App\Events\UserFollowsMetadataSet;
use App\Events\UserMetadataSet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;

class ListenRabbitMQMetadata extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:listen-metadata';
    protected $description = 'Listen for metadata messages on RabbitMQ queue';

    protected function getQueueName(): string
    {
        return 'user_metadata';
    }

    public function processMessage(AMQPMessage $msg)
    {
        $received_metadata = $msg->getBody();
        $decoded_metadata = json_decode($received_metadata, true);
        $pubkey = $decoded_metadata[2]["pubkey"];

        $metadata_set = false;

        if ($received_metadata && self::checkPubkey($pubkey)) {
            $redis_key = "{$pubkey}:metadata";
            $metadata_set = Redis::set($redis_key, $received_metadata);
            
            if ($metadata_set) {
                try {
                    event(new UserMetadataSet(true, $pubkey));
                    $this->channel->basic_ack($msg->getDeliveryTag());
                } catch (\Exception $e) {
                    $this->error('Error firing UserMetadataSet event: ' . $e->getMessage());
                    $this->channel->basic_nack($msg->getDeliveryTag(), false, false);
                }
            } else {
                $this->warn('No metadata received');
                $this->channel->basic_nack($msg->getDeliveryTag(), false, false);
            }
        } 
    }

    private static function checkPubkey($pubkey)
    {
        return Redis::exists("{$pubkey}:follows") || Redis::exists("{$pubkey}:user-notes");
    }
}
