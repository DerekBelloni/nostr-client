<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TrendingEventsManager
{
    public static function index(Request $request)
    {
        $trending_notes = self::_getTrendingNotes();
        return $trending_notes;
    }

    private static function _getTrendingNotes()
    {
        $client = new Client();

        $response = $client->request('GET', 'https://api.nostr.band/v0/trending/notes', [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        
        $body = $response->getBody();
   
        $trending_notes = json_decode($body->getContents(), true);
        $trending_notes = collect($trending_notes["notes"]);

        $processed_notes = self::_processNotes($trending_notes);

        return $processed_notes;
    }

    private static function _processNotes(&$trending_notes)
    {
        return $trending_notes->transform(function ($note) {
            if (isset($note["author"])) {
                $note["author"]["content"] = json_decode($note["author"]["content"], true);
            }
            return $note;
        });
    }
}