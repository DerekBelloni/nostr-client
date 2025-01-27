<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Repositories\ContentFormatter;
use App\Repositories\ContentProcessor;
use App\Repositories\RedisManager;
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
                $user_metadata = null;

                $user_hex_req = new Request([
                    'publicKeyHex' => $publicKeyHex
                ]);
            
                $decoded_metadata = json_decode(Redis::get("{$publicKeyHex}:metadata"), true);

                if (empty($decoded_metadata)) {
                    RabbitMQManager::userMetadataQueue($user_hex_req);
                } else {
                    $content_processor = new ContentProcessor();
                    $formatter = new ContentFormatter($content_processor);
                    $redis_manager = new RedisManager($formatter);
                    $user_metadata = $redis_manager->formatEventContent($decoded_metadata);
                }

                return [$publicKeyHex, $publicKeyBech32, $privateKeyHex, $user_metadata];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
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