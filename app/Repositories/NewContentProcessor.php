<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use swentel\nostr\Key\Key;
use function BitWasp\Bech32\decodeRaw;

class NewContentProcessor
{
    public function processContent($content, $type = null, $event_id = null, $idx = null)
    {

        if ($type == "initial") {
            return $this->parseNostrContent($content);
        }

        if ($type == "callback") {
            return $this->decodeBech32($content, $event_id, $idx);
        }
    }
    
    public function parseNostrContent($content)
    {
        $nostr_pattern = '/nostr:(?:[a-zA-Z0-9]+)/i';
        preg_match_all($nostr_pattern, $content, $matches);

        $decoded_entities = [];
        foreach($matches[0] as $match) {
            $decoded_entities[] = $match;
        }

        return $decoded_entities;
    }

    public function decodeBech32(&$content, $event_id, $idx)
    {
        $key = new Key();
        $parts = explode(':', $content);

        $identifier = explode('1', $parts[1])[0];
        $decodeType = null;

        if ($identifier == 'npub' || $identifier == 'nsec' || $identifier == 'note') {
            $decodeType = 'bareEncoding';
        } else if ($identifier == 'nprofile' || $identifier == 'naddr' || $identifier == 'nrelay' || $identifier == 'nevent') {
            $decodeType = 'extended';
        }

        $bech32Key = $parts[1];
     
        switch ($decodeType) {
            case 'bareEncoding':
                $hex = $key->convertToHex($bech32Key, $key);
                $entity = [ 'nostr_entity' => $hex, 'type' => $identifier, 'event_id' => $event_id, 'trending_idx' => $idx];
                return $entity;
            case 'extended':
                $binary = self::decodeToBase32($bech32Key, $key);
                return self::nprofileHex($binary, $identifier, $event_id, $idx);
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

    private function standardTypeTwo($value_arr) 
    {
        $hex = '';
        foreach($value_arr as $byte) {
            $decimal = bindec((int)$byte);
            $hex .= str_pad(dechex($decimal), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }

    private function standardTypeThree($value_arr)
    {
        $kind = 0;
        foreach ($value_arr as $index => $byte) {
            $decimal = bindec((int)$byte);
            $kind = ($kind << 8) | $decimal; 
        }
       
        return $kind; 
    }

    private function nprofileHex($binary, $identifier, $event_id, $idx) 
    {
        if (!is_array($binary)) {
            $binary = $binary->toArray();
        }

        $entities = [];
        $additional = $binary;

        while (!empty($additional)) {
            $type = bindec((int)$additional[0]);
            $length = bindec((int)$additional[1]);
            $value_arr = array_slice($additional, 2, $length);
            
            $value = null;
            switch ($type) {
                case 0:
                    $value = $this->standardTypeZero($value_arr);
                    break;
                case 1:
                    $value = $this->standardTypeOne($value_arr);
                    break;
                case 2:
                    $value = $this->standardTypeTwo($value_arr);
                    break;
                case 3:
                    $value = $this->standardTypeThree($value_arr);
                    break;
            }

            $entities[] = [
                'type' => $type,
                'value' => $value
            ];

            $additional = array_slice($additional, $length + 2);
        }

        $structured_entity = [
            'nostr_entity' => null,
            'type' => $identifier,
            'event_id' => $event_id,
            'trending_idx' => $idx
        ];

        foreach ($entities as $entity) {
            switch ($entity['type']) {
                case 0:
                    $structured_entity['nostr_entity'] = $entity['value'];
                    break;
                case 1:
                    if (!isset($structured_entity['relays'])) {
                        $structured_entity['relays'] = [];
                    }
                    $structured_entity['relays'][] = $entity['value'];
                    break;
                case 2:
                    $structured_entity['author'] = $entity['value'];
                    break;
                case 3:
                    $structured_entity['kind'] = $entity['value'];
                    break;
            }
        }
     
        return $structured_entity;
    }
}