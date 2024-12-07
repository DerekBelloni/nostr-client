<?php

namespace App\Http\Controllers\Trending;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TrendingEventsManager;
use App\Repositories\TrendingHashTagsManager;
use Inertia\Inertia;


class TrendingEventsController extends Controller
{
    public function index(Request $request)
    {
        list($trending_content, $trending_hashtags) = TrendingEventsManager::index($request);
        return ['trending_content' => $trending_content, 'trending_hashtags' => $trending_hashtags];
    }

    public function show(Request $request)
    {
        return TrendingHashTagsManager::getTrendingHashTags($request);
    }
}