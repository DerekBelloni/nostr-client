<?php

namespace App\Repositories;

class ContentProcessor
{
    public function processContent($content)
    {
        return $this->parseContent($content);
    }

    private function parseContent($content)
    {
        // $pattern = '/(https:\/\/[^\s]+(\.(mp4|webm|ogg|mov|jpg|jpeg|png|gif))?)/i';
        $pattern = '/<.*?src="(https:\/\/[^\s]+(\.(mp4|webm|ogg|mov|jpg|jpeg|png|gif))?)".*?>|https:\/\/[^\s]+(\.(mp4|webm|ogg|mov|jpg|jpeg|png|gif))?/i';
 
        $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
 
        $elements = [];
        foreach ($parts as $index => $part) {
            $content = $part[0];
            $offset = $part[1];

            if ($index % 2 == 0) {
                if (!empty(trim($content))) {
                    $elements[] = [
                        'type' => 'text',
                        'content' => trim($content),
                        'offset' => $offset
                    ];
                } 
            } else {
                $url = $part[0] . ($parts[$index + 1][0] ?? '');
                $elements[] = [
                    'type' => $this->determineUrlType($url),
                    'content' => $content,
                    'offset' => $offset
                ];
            }
        }

        usort($elements, [$this, 'sortOffsets']);

        return $elements;
    }   

    private function determineUrlType($content)
    {
        // be mindful or nostr notes
        $content = 'https://i.nostr.build/1K6OAeWdBt8sXksy.jpg nostr:note183lyl25g5tthxatw675f72eegmety3mppyrn0asawa5d77e7tnaqpyy72x';
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $content)) {
            return 'image';
        } else if (preg_match('/\.(mp4|webm|ogg|mov)$/i', $content)) {
            return 'video';
        } else if (preg_match_all('/https:\/\/[^\s]+|nostr:note[^\s]+/i', $content, $matches)) {
            foreach ($matches[0] as $url) {
                // If it matches a media file extension, continue to next match
                if (preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|ogg|mov)$/i', $url)) {
                    continue;
                }
                // If it matches nostr:note pattern, categorize it as 'nostr-note'
                if (preg_match('/^nostr:note[^\s]+$/i', $url)) {
                    return 'nostr-note';
                }
                // Otherwise, treat it as a link
                return 'link';
            }
        }
        else {
            return 'none';
        }
    }

    private function sortOffsets($a, $b)
    {
        if ($a["offset"] == $b["offset"]) {
            return 0;
        }

        return ($a["offset"] < $b["offset"]) ? -1 : 1;
    }
}