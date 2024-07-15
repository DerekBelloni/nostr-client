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
        $trending_notes;
        $trending_video;
        $trending_images;

        $trending_notes = TrendingEventsManager::index($request);

        return Inertia::render('Home', ['trendingNotes' => $trending_notes]);
    }
}