<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SearchedEventsManager 
{
    public static function index(Request $request)
    {
        $redis_search_cache = $request->input('redisSearchCache');
        dd($redis_search_cache);
    }
}