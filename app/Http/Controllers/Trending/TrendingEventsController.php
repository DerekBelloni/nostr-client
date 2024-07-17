<?php

namespace App\Http\Controllers\Trending;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TrendingEventsManager;
use Inertia\Inertia;

class TrendingEventsController extends Controller
{
    public function index(Request $request)
    {
        $trending_content = TrendingEventsManager::index($request);
        // dd("in controller: ", $trending_content);
        return Inertia::render('Home', ['trendingContent' => $trending_content]);
    }
}