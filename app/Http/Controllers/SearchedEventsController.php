<?php

namespace App\Http\Controllers;

use App\Repositories\SearchedEventsManager;
use Illuminate\Http\Request;

class SearchedEventsController extends Controller
{
    public function index(Request $request)
    {
        return SearchedEventsManager::index($request);
    }   
}