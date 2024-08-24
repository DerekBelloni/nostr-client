<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use swentel\nostr\Key\Key;

class RelayNotesManager 
{
    public static $metadata_ids = [];
    public static $metadata_notes = [];

    public static function getDefaultNotes(Request $request)
    {
        $trending_relays = [
            "wss://relay.damus.io-trending",
            "wss://nos.lol-trending",
            "wss://purplerelay.com-trending",
            "wss://relay.primal.net-trending"
        ];
        // $default_relays = [
        //     "wss://relay.damus.io",
        //     "wss://nos.lol",
        //     "wss://purplerelay.com",
        //     "wss://relay.primal.net"
        // ];

        $merged_notes = [];

        foreach ($trending_relays as $relay) {
           $notes = json_decode(Redis::get($relay), true);
           if (is_array($notes)) {
                $merged_notes = array_merge($merged_notes, $notes);
           }
        }
        // foreach ($default_relays as $relay) {
        //    $notes = json_decode(Redis::get($relay), true);
        //    if (is_array($notes)) {
        //         $merged_notes = array_merge($merged_notes, $notes);
        //    }
        // }
    
        return self::_processNotes($merged_notes);
    }

    private static function _processNotes($merged_notes)
    {
        $event_detail_ids = [];
        $metadata_notes = [];
        $processed_notes = [];
        $reaction_notes = [];

        foreach ($merged_notes as $note) {
            if ($note[2]["kind"] == 0) {
                $metadata_notes[] = self::_processMetadata($note[2]);
            }

            if ($note[2]["kind"] == 7) {
                $reaction_notes[] = $note[2];
            }

            $event_detail_id = $note[2]["id"];

            if (!in_array($event_detail_id, $event_detail_ids)) {
                $utc_time = Carbon::createFromTimestampUTC($note[2]["created_at"])->format('Y-m-d H:i:s');
                $note[2]["utc_time"] = $utc_time;

                $npub_id = NostrKeyManager::genNPubKey($note[2]["pubkey"]);
                $note[2]["npub"] = $npub_id;
      
                $event_detail_ids[] = $event_detail_id;
                
                if ($note[2]["kind"] == 1) {
                    $processed_notes[] = $note[2];
                }
            }
        }
        self::_storeMetadata();
        
        $processed_notes = collect($processed_notes)->sortByDesc("utc_time")->values()->all();

        $metadata_formatted_notes = collect(self::_mergeNotesWithMetadata($processed_notes))->values()->all();
    
        return [$metadata_formatted_notes, $metadata_formatted_reactions];
    }

    private static function _mergeNotesWithMetadata(&$notes)
    {
        foreach($notes as &$note) {
            $note["metadata_content"] = [];
            if (array_key_exists($note["pubkey"], self::$metadata_notes)) {
                $note["metadata_content"] = json_decode(self::$metadata_notes[$note["pubkey"]][0]["content"], true);
            }
        }
        return $notes;
    }

    private static function _processMetadata($note)
    {
        $metadata_id = $note["id"];
        
        if (!in_array($metadata_id, self::$metadata_ids)) {
            self::$metadata_notes[$note["pubkey"]][] = $note;
            self::$metadata_ids[] = $metadata_id; 
        }
    }

    private static function _storeMetadata() 
    {
        $encoded_metadata = json_encode(self::$metadata_notes);
        Redis::set('nostr_user_metadata', $encoded_metadata);
    }
}
