<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TrendingEventsManager
{
    public static function index(Request $request)
    {
        $client = new Client();

        $trending_notes = self::_getTrendingNotes($client);
        $trending_videos = self::_getTrendingVideos($client);
        $trending_images = self::_getTrendingImages($client);
        
        return $trending_notes;
    }

    private static function _getTrendingNotes($client)
    {
        $response = $client->request('GET', 'https://api.nostr.band/v0/trending/notes', [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        
        $body = $response->getBody();
   
        $trending_notes = json_decode($body->getContents(), true);
        $trending_notes = collect($trending_notes["notes"]);

        $processed_notes = self::_processContent($trending_notes);
        return $processed_notes;
    }

    private static function _getTrendingVideos($client)
    {
        $response = $client->request('GET', 'https://api.nostr.band/v0/trending/videos', [
            'header' => [
                'Accept' => 'application/json'
            ]
        ]);

        $body = $response->getBody();

        $trending_videos = json_decode($body->getContents(), true);
        $trending_videos = collect($trending_videos["videos"]);

        $processed_videos = self::_processContent($trending_videos);
        return $trending_videos;
    }

    private static function _getTrendingImages($client) 
    {
        $response = $client->request('GET', 'https://api.nostr.band/v0/trending/images', [
            'header' => [
                'Accept' => 'applicatopm/json'
            ]
        ]);

        $body = $response->getBody();

        $trending_images = json_decode($body->getContents(), true);
        $trending_images = collect($trending_images["images"]);

        $processed_images = self::_processContent($trending_images);
        return $processed_images;
    }

    private static function _processContent(&$trending_content)
    {
        return $trending_content->transform(function ($note) {
            if (isset($note["author"])) {
                $note["author"]["content"] = json_decode($note["author"]["content"], true);
            }
            return $note;
        });
    }

    
}