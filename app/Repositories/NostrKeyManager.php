<?php

namespace App\Repositories;

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
                self::_setRedisStream($publicKeyHex);

                // Convert hex public key to npub
                $publicKeyBech32 = $key->convertPublicKeyToBech32($publicKeyHex);

                return [$publicKeyHex, $publicKeyBech32];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        }
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