<?php

namespace App\Jobs;

use App\Events\UserMetadataSet;
use App\Facades\UserMetadata;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ListenUserMetadata implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($pubHexKey)
    {
        $this->pubHexKey = $pubHexKey;
    }

    // Array to string Conversion

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        
        $channel->queue_declare('metadata_set', false, false, false, false);
        Log::info('Waiting for messages on queue: metadata_set');

        $callback = function ($msg) {
            $receivedPubHexKey = $msg->body;
            Log::info('Received message', ['receivedPubHexKey' => $receivedPubHexKey]);
            $redis_metadata = json_decode(Redis::get($receivedPubHexKey), true);
        
            if (isset($redis_metadata)) {
                Log::info('Metadata found in Redis', ['metadata' => $redis_metadata]);
                try {
                    Log::info('About to fire UserMetadataSet event', ['pubHexKey' => $receivedPubHexKey]);
                    event(new UserMetadataSet($receivedPubHexKey, $redis_metadata));
                    // UserMetadataSet::dispatch($receivedPubHexKey, $redis_metadata);
                    Log::info('UserMetadataSet event fired', ['pubHexKey' => $receivedPubHexKey]);
                } catch (\Exception $e) {
                    Log::error('Error firing UserMetadataSet event', ['error' => $e->getMessage()]);
                }
            } else {
                Log::warning('No metadata found in Redis for key', ['pubHexKey' => $receivedPubHexKey]);
            }
        };

        $channel->basic_consume('metadata_set', '', false, true, false, false, $callback);

        try {
            $channel->consume();
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }
    }
}
