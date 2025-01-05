<?php

namespace App\Console\Commands;

use App\Events\SearchResultsSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenSearchResults extends BaseRabbitMQListener
{
    protected $signature = 'rabbitmq:search-results';
    protected $description = 'Listen for NIP-50 search results';

    protected function getQueueName(): string
    {
        return 'search_results';
    }

    public function processMessage(AMQPMessage $msg) 
    {
        $received_search_results = $msg->getBody();
        $decoded_search_results = json_decode($received_search_results, true);
        $search_key = $decoded_search_results["SearchKey"];
        $pubkey = null;
        $uuid = null;

        if (ctype_xdigit($search_key)) {
            $pubkey = $search_key;
        } else {
            $uuid = $search_key;
        }

        $search_key = 'search_content' . ':' . $search_key;

        $redis_key = $search_key;
        $search_results_set = Redis::sAdd($redis_key, $received_search_results);

        if ($search_results_set) {
            try {
                event(new SearchResultsSet(true, $pubkey, $uuid));
            } catch (\Exception $e) {
                $this->error('Error firing Search Results Set event: ' . $e->getMessage());
            }
        }

    }
}