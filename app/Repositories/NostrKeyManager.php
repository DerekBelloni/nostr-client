<?php

namespace App\Repositories;

use Illuminate\Http\Request;
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
            
                return $publicKeyBech32;
                // return [
                //     'privateKey' => $privateKeyHex,
                //     'publicKey' => $publicKeyHex,
                //     'bech32PublicKey' => $publicKeyBech32
                // ];
            } catch (\Exception $e) {
                Log::error('Error processing Nostr key: ' . $e->getMessage());
                dd('Error: ' . $e->getMessage());
            }
        }
    }

    public static function genKeyPair()
    {

    }

    public static function validateKeys($nsec)
    {

    }
}