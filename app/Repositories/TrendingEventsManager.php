<?php

namespace App\Repositories;

use Carbon\Carbon;
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

        return self::_mergeTrendingContent($trending_notes, $trending_images, $trending_videos);
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

        $processed_notes = self::_processContent($trending_notes, "notes");
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

        $processed_videos = self::_processContent($trending_videos, "videos");
        return $processed_videos;
    }

    private static function _getTrendingImages($client) 
    {
        $response = $client->request('GET', 'https://api.nostr.band/v0/trending/images', [
            'header' => [
                'Accept' => 'application/json'
            ]
        ]);

        $body = $response->getBody();

        $trending_images = json_decode($body->getContents(), true);
        $trending_images = collect($trending_images["images"]);

        $processed_images = self::_processContent($trending_images, "images");
        return $processed_images;
    }

    private static function _mergeTrendingContent($trending_notes, $trending_images, $trending_videos)
    {
        return $trending_notes->merge($trending_images)->merge($trending_videos);
        
        // $trending_content = $trending_notes->merge($trending_images)->merge($trending_videos);
        
        // $sorted_trending_content = $trending_content->sortByDesc('event.created_at');
        // return $sorted_trending_content->values();
    }

    private static function _processImages(&$trending_images)
    {
        $pattern = '/https:\/\/[^\s]+/i';
        $trending_images->transform(function ($item) use ($pattern) {
            if (isset($item["event"]["content"])) {
                $item["event"]["content"] = preg_replace_callback($pattern, function ($matches) {
                    $url = $matches[0];
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $url)) {
                        return '<img src="' . $url . '">';
                    } else {
                        return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                    }
                }, $item["event"]["content"]);
            }
            return $item;
        });
    }

    private static function _processVideos(&$trending_videos)
    {
        $pattern = '/https:\/\/[^s]+\.(mp4|webm|ogg|mov)/i';
        $trending_videos->transform(function ($item) use ($pattern) {
            if (isset($item["event"]["content"])) {
                $item["event"]["content"] = preg_replace_callback($pattern, function ($matches) {
                    $url = $matches[0];
                    $extension = pathinfo($url, PATHINFO_EXTENSION);
                    if (in_array($extension, ['mp4', 'webm', 'ogg', 'mov'])) {
                        return '<video width="420" height="340" controls><source src="' . $url . '" type="video/' . $extension . '">Your browser does not support the video tag.</video>';
                    }
                }, $item["event"]["content"]);
            }
            return $item;
        });
    }

    private static function _processContent(&$trending_content, $type = null)
    {
        switch ($type) {
            case "images":
                self::_processImages($trending_content);
                self::_processVideos($trending_content);
                break;
            case "notes":
                self::_processImages($trending_content);
                self::_processVideos($trending_content);
                break;
            case "videos":
                self::_processVideos($trending_content);
                break;
        }

        return $trending_content->transform(function ($note) {
            if (isset($note["author"])) {
                $note["author"]["content"] = json_decode($note["author"]["content"], true);
            }
            $note["event"]["utc_timestamp"] = Carbon::createFromTimestampUTC($note["event"]["created_at"])->format('Y-m-d H:i:s');
            return $note;
        });
    }

    
}