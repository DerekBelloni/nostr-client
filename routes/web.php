<?php

use App\Http\Controllers\NostrKeyController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RedisController;
use App\Http\Controllers\Trending\TrendingEventsController;
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

Route::get('/notes', [NotesController::class, 'show']);

Route::post('/npub', [NostrKeyController::class, 'login']);

Route::get('/trending-events', [TrendingEventsController::class, 'index']);

require __DIR__.'/auth.php';
