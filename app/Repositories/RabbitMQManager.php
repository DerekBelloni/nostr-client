<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQManager 
{
    public static function testQueue(Request $request)
    {
        // dd($request->all());
        $user_pub_hex = $request->input('user_pub_hex');
        // dd($user_pub_hex);
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

        $channel = $connection->channel();
        $channel->queue_declare('user_pub_key', false, false, false, false);

        $message = new AMQPMessage($user_pub_hex);

        $channel->basic_publish($message, '', 'user_pub_key');
        
        $channel->close();
        $connection->close();
        return 'complete';
    }
}