<?php

namespace App\Console\Commands;

use App\Events\RelayNotesReceived;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use function Ratchet\Client\connect;
use Ramsey\Uuid\Uuid;

class NostrRelay extends Command
{
    protected $signature = 'nostr:relay-client';
    protected $description = 'Connect to a Nostr relay and listen for messages';

    public function handle()
    {
        $url = 'wss://relay.damus.io';

        // Create a unique subscription ID
        $subscriptionId = Uuid::uuid4()->toString();

        // Define the subscription criteria
        $subscriptionMessage = json_encode([
            "REQ",
            $subscriptionId, // A unique ID for this subscription
            [
                "kinds" => [1], // Event kind 1 (text notes)
                "since" => time() - 3600 // Events from the last hour
            ]
        ]);


        connect($url)->then(function($conn) use ($subscriptionMessage, &$notes) {
            $conn->on('message', function($msg) use (&$notes) {
                $message = json_decode($msg, true);

                if (is_array($message) && isset($message[0])) {
                    if ($message[0] === 'EOSE') {
                        Log::info("End of Stored Events received for subscription: {$message[1]}");
                        Log::info("Collected notes: " . json_encode($notes));
                        event(new RelayNotesReceived($notes));
                        $notes = [];
                    } else if ($message[0] === 'EVENT') {
                        $eventData = $message[2];
                        // Log::info("Event received: " . json_encode($eventData));
                    
                        if (isset($eventData['content'])) {
                            Log::info("Event content: " . json_encode($eventData));
                            // $notes[] = $eventData['content'];
                            $notes[] = $eventData;
                        } else {
                            Log::info("Event content: No content found");
                        }
                    } else {
                        Log::info("Unknown message type received: " . json_encode($message));
                    }
                } else {
                    Log::info("Invalid message format received: {$msg}");
                }
            });

            $conn->on('close', function($code = null, $reason = null) {
                Log::info("Connection closed ({$code} - {$reason})");
            });

           // Send the subscription message
            $conn->send($subscriptionMessage);


        }, function($e) {
            Log::error("Could not connect: {$e->getMessage()}");
        });
    }
}
