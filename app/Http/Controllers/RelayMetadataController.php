<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RelayMetadataController extends Controller
{
    public function get(Request $request) 
    {
        $client = new Client();

        $response = $client->get('https://relay.nostr.band', [
            'headers' => [
                'Accept' => 'application/nostr+json'
            ]
        ]);
        dd(json_decode($response->getBody()->getContents(), true));
    }
}