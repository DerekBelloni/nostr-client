<?php

namespace App\Http\Controllers;

use App\Repositories\NostrKeyManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NostrKeyController extends Controller 
{
    public static function login(Request $request)
    {
        // list($metadata_content, $hexPub, $npub, $verified, $hexPriv) = NostrKeyManager::login($request);
        list($hexPub, $npub, $hexPriv) = NostrKeyManager::login($request);

        // return Inertia::render('Home', ['metadataContent' => $metadata_content, 'npub' => $npub, 'hexPub' => $hexPub, 'hexPriv' => $hexPriv ,'verified' => $verified]);
        return Inertia::render('Home', ['npub' => $npub, 'hexPub' => $hexPub, 'hexPriv' => $hexPriv]);
    }

    public static function authenticate(Request $request)
    {
        $verified = NostrKeyManager::authenticateNip05($request);
        return Inertia::render('Home', ['verified' => $verified]);
    }
}