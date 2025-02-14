<?php

namespace App\Repositories;

use swentel\nostr\Key\Key;
use function BitWasp\Bech32\decodeRaw;

class NewContentProcessor
{
    public function processContent($content, $type = null)
    {

        if ($type == "initial") {
            return $this->parseNostrContent($content);
        }

        if ($type == "callback") {
            return $this->decodeBech32($content);
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

        // I will want to return to the client and call back to this functionality with a loader inside whatever is embedded
        // $bech32content = 'nostr:nprofile1qqsrhuxx8l9ex335q7he0f09aej04zpazpl0ne2cgukyawd24mayt8gpp4mhxue69uhhytnc9e3k7mgpz4mhxue69uhkg6nzv9ejuumpv34kytnrdaksjlyr9p';
        // foreach ($decoded_entities as $entity) {
        //     $this->decodeBech32($entity);
        // } 
        return $decoded_entities;
    }

    public function decodeBech32(&$content)
    {
        $key = new Key();
        $parts = explode(':', $content);
        // dd($parts);
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
                $entity = [ 'nostr_entity' => $hex, 'type' => $identifier];
                return $entity;
            case 'extended':
                $binary = self::decodeToBase32($bech32Key, $key);
                return self::nprofileHex($binary, $identifier);
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

    private function nprofileHex($binary, $identifier) 
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

    // After collecting all TLVs, structure the final entity
    $structured_entity = [
        'nostr_entity' => null,
        'type' => $identifier
    ];

    // Process collected entities in proper order
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
    // dd($structured_entity);
    return $structured_entity;
}

    // private function nprofileHex($binary, $identifier)
    // {
    //     $built_arr = [];
    //     $iteration = 0;
    //     $additional = $binary;
     
    //     $build_arr = function($binary) use (&$additional, &$built_arr, $identifier, &$iteration) {
    //         $additional = [];
    //         $value = null;
    //         $type = bindec((int)$binary[0]);
    //         // dd($type);
    //         $length = bindec((int)$binary[1]);
         
    //         if (!is_array($binary)) {
    //             $binary = $binary->toArray();
    //         }
         
    //         $value_arr = array_slice($binary, 2, $length);

    //         if ($type == 0) {
    //             $value = self::standardTypeZero($value_arr);
    //             $structured_entity = [
    //                 'nostr_entity' => $value,
    //                 'type' => $identifier
    //             ];
    //             return $structured_entity;
    //         }

    //         if ($type == 1) {
    //             // dd($value_arr);
    //             $values[] = self::standardTypeOne($value_arr);
    //             dd($values, $identifier);
    //             $structured_entity = [
    //                 'nostr_entity' => $value,
    //                 'type' => $identifier
    //             ];
    //             return $structured_entity;
    //         }

    //         if ($type == 2) {
    //             $value = self::standardTypeTwo($value_arr, $type);
    //             $structured_entity = [
    //                 'nostr_entity' => $value,
    //                 'type' => $identifier
    //             ];
    //             return $structured_entity;
    //         }

    //         if ($type == 3) {
    //             $value = self::standardTypeThree($value_arr);
    //             $structured_entity = [
    //                 'nostr_entity' => $value,
    //                 'type' => $identifier
    //             ];
    //             return $structured_entity;
    //         }
          
    //         $built_arr[$iteration]['type'] = $type;
    //         $built_arr[$iteration]['value'] = $value;
    //         $additional = array_slice($binary, $length + 2);
    //     };

    //     while (!empty($additional)) {
    //         $entity = $build_arr($additional);
    //         if ($entity) {
    //             return $entity;
    //         }
    //         $iteration++;
    //     }
    //     // dd($build_arr);
    //     return $build_arr;
    // }
}