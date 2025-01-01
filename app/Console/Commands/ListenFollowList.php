<?php

namespace App\Console\Commands;

use App\Events\UserFollowList;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;

class ListenFollowList extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:follow-list';
    protected $description = 'Listen for a users follow list on RabbitMQ queue';

    protected function getQueueName(): string
    {
        return 'follow_list';
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $received_follows = $msg->getBody();
        $decoded_follows = json_decode($received_follows, true);
        $pubkey = $decoded_follows[2]["pubkey"];
        
        if ($received_follows) {
            $redis_key = "{$pubkey}:follows";
            $follows_set = Redis::set($redis_key, $received_follows);
        }

        if ($follows_set) {
            try {
                event(new UserFollowList(true, $pubkey));
                $this->info("User follows list event fired: " . $follows_set);
            } catch (\Exception $e) {
                $this->error('Error firing user follows list event: ' . $e->getMessage());
            }
        } else {
            $this->warn('No follows received');
        } 
    }
}