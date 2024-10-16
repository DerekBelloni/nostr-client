<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Str;

class RabbitMQManager 
{
    // maybe use a constructor to instantiate the connection and channel
    public static function userMetadataQueue(Request $request)
    {
        $pub_hex_key = $request->input('user_pub_hex');

        if (!is_null($pub_hex_key)) {
            $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
            $channel = $connection->channel();
            $uuid = Str::uuid();
            $pub_key_UUID = $pub_hex_key . ':' . $uuid;
            $channel->queue_declare('user_pub_key', false, false, false, false);
    
            $message = new AMQPMessage($pub_key_UUID);
            $channel->basic_publish($message, '', 'user_pub_key');
            
            $channel->close();
            $connection->close();
            return 'complete';
        } else {
            return 'no pubkey';
        }
    }

    public static function followMetadataQueue(Request $request) 
    {
        $pub_key_hex = $request->input('publicKeyHex');
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $uuid = Str::uuid();
        $pub_key_UUID = $pub_key_hex . ':' . $uuid;
        $channel->queue_declare('follow_list_metadata', false, false, false, false,);

        $message = new AMQPMessage($pub_key_UUID);
        $channel->basic_publish($message, '', 'follow_list_metadata');
     
        $channel->close();
        $connection->close();
        return 'complete';
    }

    public static function newNoteQueue(Request $request)
    {
        $note_content = $request->input('noteContent');
        $pub_hex_key = $request->input('pubHexKey');
        $priv_hex_key = $request->input('hexPriv');

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('new_note', false, false, false, false,);

        // $formatted_body = $pub_hex_key . ':' . $note_content;
        $params = [
            'privHexKey' => $priv_hex_key,
            'pubHexKey' => $pub_hex_key,
            'kind' => 1,
            'content' => $note_content
        ];

        $message = new AMQPMessage(json_encode($params), true);

        $channel->basic_publish($message, '', 'new_note');

        $channel->close();
        $connection->close();
        return 'complete';
    }
}