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
        $pattern = '/<.*?src="(https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp))".*?>(?![^<]*<\/\w+>)|https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp)\b|(nostr:(?:[a-zA-Z0-9]{59}))/i';

        $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);

        $elements = [];
   
        foreach ($parts as $index => $part) {
            $content = $part[0];
            $offset = $part[1];

            if ($index % 2 == 0) {
                if (!empty(trim($content))) {
                    $elements[] = [
                        // 'type' => 'text',
                        'type' => $this->determineUrlType($content),
                        'content' => trim($content),
                        'offset' => $offset
                    ];
                } 
            } else {
                $url = $part[0];
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
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $content)) {
            return 'image';
        } else if (preg_match('/\.(mp4|webm|ogg|mov)$/i', $content)) {
            return 'video';
        }  else if (preg_match('/https?:\/\/[^\s]+/i', $content)) {
            return 'link';
        } else if (preg_match('/nostr:(?:[a-zA-Z0-9]{59})/i', $content)) {
            dd($content);
            return 'nostr-note';
        } else {
            return 'text';
        }
    }

    private function retrieveSmartPreviewData($url)
    {
        $client = new Client();

        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $html = $response->getBody()->getContents();

        $crawler = new Crawler($html);

        $metadata = [];
        $metadata["title"] = $crawle->filterXPath('//meta[@property="og:title"]')->attr('content') ?? '';
        $metadata["description"] = $crawler->filterXPath('//meta[@property="og:description"]')->attr('content') ?? '';
        $metadata["url"] = $crawler->filterXPath('//meta[@property="og:url"]')->attr('content') ?? '';
        $metadata["image"] = $crawler->filterXPath('//meta[@property="og:image"]')->attr('content') ?? '';

        // dd($metadata);
    }

    private function sortOffsets($a, $b)
    {
        if ($a["offset"] == $b["offset"]) {
            return 0;
        }

        return ($a["offset"] < $b["offset"]) ? -1 : 1;
    }
}