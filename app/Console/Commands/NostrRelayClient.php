<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ratchet\Client\WebSocket;
use Ratchet\Client\connect;

use function Ratchet\Client\connect;

class NostrRelayClient extends Command
{
    protected $signature = 'nostr:relay-client';
    protected $description = 'Connect to a Nostr relay and listen for messages';

    public function handle()
    {
        $url = 'wss://nostrsatva.net';

        connect($url)->then(function($conn) {
            $conn->on('message', function($msg) {
                dd($msg);
                Log::info("Received: {$msg}");
                // Process the message here, possibly broadcasting an event
            });

            $conn->on('close', function($code = null, $reason = null) {
                Log::info("Connection closed ({$code} - {$reason})");
            });

            // Example subscription request
            // $subscriptionMessage = json_encode(["REQ", "unique_id", ["event", ["limit" => 10]]]);
            $conn->send($subscriptionMessage);

        }, function($e) {
            Log::error("Could not connect: {$e->getMessage()}");
        });
    }
}
