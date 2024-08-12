<?php

namespace App\Services\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserMetadataService
{
    public function listenForMetadata($pubHexKey)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        
        $channel->queue_declare('metadata_set', false, false, false, false);

        echo "[*] Waiting for messages. To exist press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume('metadata_set', '', false, true, false, false, $callback);

        try {
            $channel->consume();
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }
    }

    public function handleMetadataNotification()
    {

    }
}