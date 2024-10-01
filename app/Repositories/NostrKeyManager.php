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

                $user_hex_req = new Request([
                    'user_pub_hex' => $publicKeyHex
                ]);
            
                // Start checking for cached metadta, call to its own function
                RabbitMQManager::userMetadataQueue($user_hex_req);

                return [$publicKeyHex, $publicKeyBech32, $privateKeyHex];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        } else {
            return response()->json(['error' => 'No nsec provided'], 400);
        }
    }

    public static function authenticateNip05(Request $request)
    {
        $metadata = $request->input('metadataContent');
        $publicKeyHex = $request->input('publicKeyHex');
        list($name, $domain) = self::_processUserMetadata($metadata);

        return self::_getNip05Verification($name, $domain, $publicKeyHex);
    }

    private static function _getNip05Verification($name, $domain, $publicKeyHex)
    {
        $client = new Client();

        $url = "https://{$domain}/.well-known/nostr.json?name={$name}";
        dd($url);
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

    private static function _checkCachedMetadata($publicKeyHex, $metadata_content)
    {
        // $cached_metadata = json_decode(Redis::get($publicKeyHex), true);
      
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

        if (is_array($cached_metadata) && isset($cached_metadata["nip05"])) {
            $nip05 = $cached_metadata["nip05"];
        }
        
        if (isset($nip05)) {
            $splitNip05 = explode('@', $nip05);
            $name = $splitNip05[0];
            $domain = $splitNip05[1];
        }

        return [$name, $domain];
    }

    public static function genNPubKey($publicKeyHex)
    {
        $key = new Key();
        return $key->convertPublicKeyToBech32($publicKeyHex);
    }
}