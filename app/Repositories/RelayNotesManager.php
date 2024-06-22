<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class RelayNotesManager 
{
    public static function getDefaultNotes(Request $request)
    {
        $default_relays = [
            'wss://nos.lol',
            'wss://relay.damus.io'
        ];

        $merged_notes = [];

        foreach ($default_relays as $relay) {
           $notes = json_decode(Redis::get($relay), true);
           if (is_array($notes)) {
                $merged_notes = array_merge($merged_notes, $notes);
           }
        }
        
        return self::_processNotes($merged_notes);
    }

    private static function _processNotes($merged_notes)
    {
        $eventDetailIds = [];
        $processed_notes = [];

        foreach ($merged_notes as $note) {
            $eventDetailId = $note[2]["id"];

            if (!in_array($eventDetailId, $eventDetailIds)) {
                $processed_notes[] = $note[2];
                $eventDetailIds[] = $eventDetailId;
            }
        }

        return $processed_notes;
    }
}