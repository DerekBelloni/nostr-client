<?php

namespace App\Http\Controllers;

use App\Repositories\NostrKeyManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NostrKeyController extends Controller 
{
    public static function login(Request $request)
    {

        list($metadata_content, $hexPub, $npub, $verified) = NostrKeyManager::login($request);

        return Inertia::render('Home', ['metadataContent' => $metadata_content, 'npub' => $npub, 'hexPub' => $hexPub, 'verified' => $verified]);
    }
}