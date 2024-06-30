<?php

namespace App\Http\Controllers;

use App\Repositories\NostrKeyManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NostrKeyController extends Controller 
{
    public static function login(Request $request)
    {
        // TODO:
        // Validate only the nsec field
        // $request->validate([
        //     'nsec' => 'required|string',
        // ]);

        $npub = NostrKeyManager::login($request);
      
        return Inertia::render('Home', ['npub' => $npub]);
    }
}