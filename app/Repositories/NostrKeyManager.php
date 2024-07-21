<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
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

                $cachedMetadata = self::_checkCachedMetadata($publicKeyHex);
                dd($cachedMetadata);
                if (isset($cachedMetadata)) {
                    list($name, $domain) = self::_processUserMetadata($cachedMetadata);
                    $verified = self::_getNip05Verification($name, $domain);
                } else {
                    self::_setRedisStream($publicKeyHex);
                }

                return [$publicKeyHex, $publicKeyBech32];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        }
    }

    private static function _getNip05Verification($name, $domain)
    {
        $client = new Client();
        $url = sprintf("https://%s/.well-known/nostr.json?name=%s", $domain, $name);
        dd($url, $domain, $name);
        $response = $client->request('GET', $url, [
            'headrs' => [
                'Accept' => 'application/json'
            ]
        ]);

        $body = $response->getBody();

        dd(json_decode($body->getContents(), true));
    }

    private static function _checkCachedMetadata($publicKeyHex)
    {
        $cachedMetadata = json_decode(Redis::get($publicKeyHex), true);
        if (isset($cachedMetadata)) {
            $cachedMetadata[2]["content"] = json_decode($cachedMetadata[2]["content"], true);

        }
        // dd($cachedMetadata);
        return $cachedMetadata;
    }

    private static function _processUserMetadata($cachedMetadata)
    {
        $nip05 = null;
        $name = null;
        $domain = null;
        foreach ($cachedMetadata as $data) {
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