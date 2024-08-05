<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use swentel\nostr\Key\Key;

class NostrKeyManager
{
    public static function login(Request $request)
    {
        $nsec = $request->input('nsec');

        if (isset($nsec)) {
            try {
                $key = new Key();
                // Convert nsec to hex private key
                $privateKeyHex = $key->convertToHex($nsec);
                // Generate public key from private key
                $publicKeyHex = $key->getPublicKey($privateKeyHex);
                // Convert hex public key to npub
                $publicKeyBech32 = $key->convertPublicKeyToBech32($publicKeyHex);

                // $cached_metadata = self::_checkCachedMetadata($publicKeyHex);
            
                // if (isset($cached_metadata)) {
                //     list($name, $domain) = self::_processUserMetadata($cached_metadata);
                //     $verified = self::_getNip05Verification($name, $domain, $publicKeyHex);
                    
                // } else {
                //     // self::_setRedisStream($publicKeyHex);
                //     $test = RabbitMQManager::testQueue($publicKeyHex);
                // }
                $user_hex_req = new Request([
                    'user_pub_hex' => $publicKeyHex
                ]);
                // dd($publicKeyHex);
                $test = RabbitMQManager::testQueue($user_hex_req);

                dd($test);
                // $metadata_content = $cached_metadata[2];

                return [$metadata_content, $publicKeyHex, $publicKeyBech32, $verified];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        }
    }

    private static function _getNip05Verification($name, $domain, $publicKeyHex)
    {
        $client = new Client();

        $url = "https://{$domain}/.well-known/nostr.json?name={$name}";

        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $body = $response->getBody();

        $data = json_decode($body->getContents(), true);

        if (isset($data['names'][$name])) {
            return $data['names'][$name] == $publicKeyHex; 
        } 
    }

    private static function _checkCachedMetadata($publicKeyHex)
    {
        $cached_metadata = json_decode(Redis::get($publicKeyHex), true);
      
        if (isset($cached_metadata)) {
            $cached_metadata[2]["content"] = json_decode($cached_metadata[2]["content"], true);

        }
      
        return $cached_metadata;
    }

    private static function _processUserMetadata($cached_metadata)
    {
        $nip05 = null;
        $name = null;
        $domain = null;
        foreach ($cached_metadata as $data) {
            if (is_array($data) && isset($data["content"]["nip05"])) {
                $nip05 = $data["content"]["nip05"];
            }
        }
        
        if (isset($nip05)) {
            $splitNip05 = explode('@', $nip05);
            $name = $splitNip05[0];
            $domain = $splitNip05[1];
        }

        return [$name, $domain];
    }

    private static function _setRedisStream($publicKeyHex)
    {
        $streamKey = 'user_pubkey_stream';

        $fields = [
            'public_key' => $publicKeyHex
        ];
        $response = Redis::xAdd($streamKey, '*', $fields);
        dd($response);
    }

    public static function genKeyPair()
    {

    }

    public static function genNPubKey($publicKeyHex)
    {
        $key = new Key();
        return $key->convertPublicKeyToBech32($publicKeyHex);
    }

    public static function validateKeys($nsec)
    {

    }
}