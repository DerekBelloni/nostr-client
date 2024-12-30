<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SearchedEventsManager 
{
    public static function index(Request $request)
    {
        dd($request->all());
    }
}