<?php

namespace App\Console\Commands;

use App\Events\AuthorMetadataSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Message\AMQPMessage;

class ListenAuthorMetadata extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:author-metadata';
    protected $description = 'Listen for searched author metadata results';

    protected function getQueueName(): string 
    {
        return 'author_metadata';
    }

    public function processMessage(AMQPMessage $msg)
    {
        $recieved_author_metadata = $msg->getBody();
        $decoded_author_metadata = json_decode($recieved_author_metadata, true);
        Log::info('author metadata msg', [$decoded_author_metadata]);
        $search_key = $decoded_author_metadata["SearchKey"];
        $pubkey = null;
        $uuid = null;


        if (ctype_xdigit($search_key)) {
            $pubkey = $search_key;
        } else {
            $uuid = $search_key;
        }
        
        $search_key =  'author_content' . ':' . $search_key;

        $redis_key = $search_key;
        $author_metadata_set = Redis::sAdd($redis_key, $recieved_author_metadata);
        Log::info("author metadata set: ", [$author_metadata_set]);
        if ($author_metadata_set) {
            try {
                event(new AuthorMetadataSet(true, $pubkey, $uuid));
            } catch (\Exception $e) {
                $this->error('Error firing Author Metadata Set event: ' . $e->getMessage());
            }
        }
    }
}