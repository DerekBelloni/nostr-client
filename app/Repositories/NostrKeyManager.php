<?php

namespace App\Repositories;

use App\Facades\UserMetada;
use App\Facades\UserMetadata;
use App\Jobs\ListenUserMetadata;
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
                $privateKeyHex = $key->convertToHex($nsec);
                $publicKeyHex = $key->getPublicKey($privateKeyHex);
                $publicKeyBech32 = $key->convertPublicKeyToBech32($publicKeyHex);

                // $cached_metadata = self::_checkCachedMetadata($publicKeyHex);
                $verified = null;
                $metadata_content = null;

                // if (isset($cached_metadata)) {
                //     list($name, $domain) = self::_processUserMetadata($cached_metadata);
                //     $verified = self::_getNip05Verification($name, $domain, $publicKeyHex);
                //     $metadata_content = $cached_metadata[2] ?? null;
                // } else {
                    $user_hex_req = new Request([
                        'user_pub_hex' => $publicKeyHex
                    ]);
                    
                    RabbitMQManager::testQueue($user_hex_req);
                // }


                return [$metadata_content, $publicKeyHex, $publicKeyBech32, $verified];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        } else {
            return response()->json(['error' => 'No nsec provided'], 400);
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