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
        $pattern = '/<.*?src="(https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp))".*?>(?![^<]*<\/\w+>)|https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp)\b|(nostr:(?:[a-zA-Z0-9]+))/i';

        $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);

        $elements = [];
   
        foreach ($parts as $index => $part) {
            $content = $part[0];
            $offset = $part[1];

            if ($index % 2 == 0) {
                if (!empty(trim($content))) {
                    $elements[] = [
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
        } else if (preg_match('/nostr:(?:[a-zA-Z0-9]+)/i', $content)) {
            return self::decodeBech32($content);
        } else {
            return 'text';
        }
    }

    private function decodeBech32(&$content)
    {
        $key = new Key();
        $parts = explode(':', $content);
        $identifier = explode('1', $parts[1])[0];
        $decodeType = null;

        if ($identifier == 'npub' || $identifier == 'nsec' || $identifier == 'note') {
            $decodeType = 'bareEncoding';
        } else if ($identifier == 'nprofile' || $identifier == 'naddr' || $identifier == 'nrealy' || $identifier == 'nrevent') {
            $decodeType = 'extended';
        }


        $bech32Key = $parts[1];
     
        switch ($decodeType) {
            case 'bareEncoding':
                return $key->convertToHex($bech32Key);
            case 'nprofile':
                $binary = self::decodeToBase32($bech32Key, $key);
                return self::nprofileHex($binary);
            default:
                $hex = null;
        }
    }

    private function decodeToBase32($bech32Key) {
        $decimal_vals = decodeRaw($bech32Key);
        $five_bit_arr = [];
        
        foreach($decimal_vals[1] as $decimal) {
            $five_bit_arr[] = self::decimalTo5Bit($decimal);
        }  
        return self::fiveBitToByte($five_bit_arr);
    }

    private function decimalTo5Bit($decimal)
    {
        $decimal = (int)$decimal;
        $binaryNumber = '';
        if ($decimal == 0) {
            $binaryNumber = '0';
        } else {
            $binaryNumber = self::decimalTo5Bit((int)($decimal / 2)) . ($decimal % 2);
        }
       
        $binaryNumber = ltrim($binaryNumber, '0'); 
        if ($binaryNumber === '') {
            $binaryNumber = '0';
        }
        return str_pad($binaryNumber, 5, '0', STR_PAD_LEFT);
    }

    private function fiveBitToByte($five_bit_arr) 
    {
        $eight_bit_arr = [];
        $temp = '';
        for($x = 0; $x < count($five_bit_arr); $x++) {
            for ($y = 0; $y < 5; $y++) {
                if (strlen($temp) < 8) {
                    $temp .= $five_bit_arr[$x][$y];
                    if (strlen($temp) === 8) {
                        $eight_bit_arr[$x] = $temp;
                        $temp = '';
                    }
                }
            }
        }

        $structured_byte_arr = collect($eight_bit_arr)->values(); 
        return $structured_byte_arr;
    }


 

    private function standardTypeZero($value_arr) 
    {
        $hex = '';
        foreach($value_arr as $byte) {
            $decimal = bindec((int)$byte);
            $hex .= str_pad(dechex($decimal), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }

    private function standardTypeOne($value_arr) 
    {
        $ascii_string = '';
        foreach($value_arr as $byte) {
            $decimal = bindec((int)$byte);
            $ascii_string .= chr($decimal);
        }
   
        return $ascii_string;
    }

    private function standardTypeTwo($value_arr, $identifier) 
    {

    }

    private function nprofileHex($binary)
    {
        $built_arr = [];
        $iteration = 0;
        $additional = $binary;
        // dd($binary);
        $build_arr = function($binary) use (&$additional, &$built_arr, &$iteration) {
            $additional = [];
            $value = null;
            $type = bindec((int)$binary[0]);
            $length = bindec((int)$binary[1]);
         
            if (!is_array($binary)) {
                $binary = $binary->toArray();
            }
         
            $value_arr = array_slice($binary, 2, $length);

            if ($type == 0) {
               $value = self::standardTypeZero($value_arr);
            }

            if ($type == 1) {
                $value = self::standardTypeOne($value_arr);
            }

            if ($type == 2) {
                $value = self::standardTypeTwo($value_arr, $type);
            }

            if ($type == 3) {
                $value = self::standardTypeThree($value_arr);
            }
          
            $built_arr[$iteration]['type'] = $type;
            $built_arr[$iteration]['value'] = $value;
            $additional = array_slice($binary, $length + 2);
        };

        while (!empty($additional)) {
            $build_arr($additional);
            $iteration++;
        }
        return $build_arr;
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
    }

    private function sortOffsets($a, $b)
    {
        if ($a["offset"] == $b["offset"]) {
            return 0;
        }

        return ($a["offset"] < $b["offset"]) ? -1 : 1;
    }
}