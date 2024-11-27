<?php

namespace App\Http\Controllers;

use App\Repositories\RabbitMQManager;
use App\Repositories\RelayNotesManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotesController extends Controller
{
    public function show(Request $request) 
    {
        $notes = RelayNotesManager::getDefaultNotes($request);
        return Inertia::render('Home', ['notes' => $notes]);
    }

    public function create(Request $request) 
    {
        RabbitMQManager::newNoteQueue($request);
        return Inertia::render('Home');
    }
}