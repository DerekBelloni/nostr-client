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

        Log::info('received metadata for pubkey: ', [$pubkey]);

        $metadata_set = false;
        $follows_set = false;

        if ($received_metadata && self::checkPubkey($pubkey)) {
            $redis_key = "{$pubkey}:metadata";
            $metadata_set = Redis::set($redis_key, $received_metadata);

            if ($metadata_set) {
                try {
                    event(new UserMetadataSet(true, $pubkey));
                } catch (\Exception $e) {
                    $this->error('Error firing UserMetadataSet event: ' . $e->getMessage());
                }
            } else {
                $this->warn('No metadata received');
            }
        } else {
            $redis_key = "follows_metadata";
            if (!self::checkExistingFollows($redis_key, $pubkey)) {
                $follows_set = Redis::sAdd($redis_key, $received_metadata);
            } else {
                $follows_set = true;
                $this->info("Follows already set");
            }
            
            if ($follows_set) {
                try {
                    event(new UserFollowsMetadataSet(true));
                    $this->info("UserFollowsMetadataSet event fired");
                } catch (\Exception $e) {
                    $this->error('Error firing UserFollowsMetadataSet event: ' . $e->getMessage());
                }
            }
        }
    }

    private static function checkPubkey($pubkey)
    {
        return Redis::exists("{$pubkey}:follows") || Redis::exists("{$pubkey}:user-notes");
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
