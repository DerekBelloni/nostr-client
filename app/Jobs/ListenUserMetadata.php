<?php

namespace App\Jobs;

use App\Events\UserMetadataSet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;

use function Pest\Laravel\json;

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        
        $channel->queue_declare('metadata_set', false, false, false, false);

        echo "[*] Waiting for messages. To exist press CTRL+C\n";

        $callback = function ($msg) {
            $pubHexKey = $msg->body;
            // dd($pubHexKey);
            $redis_metadata = json_decode(Redis::get($pubHexKey), true);
            dd($redis_metadata);
            if (isset($redis_metadata)) {
                event(new UserMetadataSet($this->pubHexKey, $redis_metadata));
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
