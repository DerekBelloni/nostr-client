<?php

namespace App\Http\Controllers;

use App\Repositories\RelayNotesManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class NotesController extends Controller
{
    public function show(Request $request) 
    {
        $notes = RelayNotesManager::getDefaultNotes($request);
        
        return Inertia::render('Home', ['notes' => $notes]);
    }
}