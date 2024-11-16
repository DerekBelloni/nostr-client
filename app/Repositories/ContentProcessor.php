<?php

namespace App\Repositories;

use swentel\nostr\Key\Key;
use function BitWasp\Bech32\decodeRaw;

class ContentProcessor
{
    public function processContent($content)
    {
        return $this->parseContent($content);
    }

    private function parseContent($content)
    {
        $pattern = '/<.*?src="(https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp))".*?>(?![^<]*<\/\w+>)|https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp)\b|(nostr:(?:[a-zA-Z0-9]{63}))/i';

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
                    'type' => $this->determineUrlType($url, $content),
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
        } else if (preg_match('/nostr:(?:[a-zA-Z0-9]{63})/i', $content)) {
            self::decodeBech32($content);
            return 'nostr-note';
        } else {
            return 'text';
        }
    }

    private function decodeToBase32($bech32Key, $key) {
        $parts = explode('1', $bech32Key);
        // $char_set = ''
        // $trimmed_part = trim($parts[1]);
        dd(decodeRaw($bech32Key));
    }

    private function convertBech32ToBinary(&$content)
    {

    }

    private function decodeBech32(&$content)
    {
        $key = new Key();
        $parts = explode(':', $content);
        // $identifier = explode('1', $parts[1])[0];
        $identifier = 'nprofile';
        // $bech32Key = $parts[1];
        $bech32Key = 'nprofile1qqsrhuxx8l9ex335q7he0f09aej04zpazpl0ne2cgukyawd24mayt8gpp4mhxue69uhhytnc9e3k7mgpz4mhxue69uhkg6nzv9ejuumpv34kytnrdaksjlyr9p';
        $hex = null;
     
        switch ($identifier) {
            case 'npub':
                $hex = $key->convertToHex($bech32Key);
                break;
            case 'nprofile':
                $binary = self::decodeToBase32($bech32Key, $key);
            default:
                $hex = null;
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