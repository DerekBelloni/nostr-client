<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class NotesController extends Controller
{
    public function show() 
    {
        $notes = json_decode(Redis::get('damus-notes'), true);
        return Inertia::render('Home', ['notes' => $notes]);
    }
}