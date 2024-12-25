<?php

namespace App\Http\Controllers;

use App\Repositories\NostrKeyManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NostrKeyController extends Controller 
{
    public static function login(Request $request)
    {
        list($hexPub, $npub, $hexPriv) = NostrKeyManager::login($request);

        return Inertia::render('Home', ['npub' => $npub, 'hexPub' => $hexPub, 'hexPriv' => $hexPriv]);
    }

    public static function authenticate(Request $request)
    {
        $verified = NostrKeyManager::authenticateNip05($request);
        return ['verified' => $verified];
        // $verified = NostrKeyManager::authenticateNip05($request);
        // return Inertia::render('Home', ['verified' => $verified]);
    }
}