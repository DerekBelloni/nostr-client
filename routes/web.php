<?php

use App\Http\Controllers\NostrKeyController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RedisController;
use App\Http\Controllers\Trending\TrendingEventsController;
use App\Http\Controllers\SSEController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Notes Controller
Route::get('/notes', [NotesController::class, 'show']);
Route::post('/note/create', [NotesController::class, 'create']);

// Nostr Key Controller
Route::post('/nip05-verification', [NostrKeyController::class, 'authenticate']);
Route::post('/npub', [NostrKeyController::class, 'login']);

// Redis Controller
Route::post('/redis/user-metadata', [RedisController::class, 'userMetadata']);

// Trending Events Controller
Route::get('/trending-events', [TrendingEventsController::class, 'index']);

require __DIR__.'/auth.php';
