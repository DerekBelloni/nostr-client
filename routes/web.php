<?php

use App\Http\Controllers\NostrKeyController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RabbitMQController;
use App\Http\Controllers\RedisController;
use App\Http\Controllers\Trending\TrendingEventsController;
use App\Repositories\RabbitMQManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

// Notes Controller
Route::get('/notes', [NotesController::class, 'show']);
Route::post('/note/create', [NotesController::class, 'create']);

// Nostr Key Controller
Route::post('/nip05-verification', [NostrKeyController::class, 'authenticate']);
Route::post('/npub', [NostrKeyController::class, 'login']);

// RabbitMQ Controller
Route::post('/rabbit-mq/follows-metadata', [RabbitMQController::class, 'getFollowsMetadata']);
Route::post('/rabbit-mq/search-results', [RabbitMQController::class, 'getSearchResults']);
Route::post('/rabbit-mq/follow-notes', [RabbitMQController::class, 'getFollowNotes']);

// Redis Controller
Route::post('/redis/user-metadata', [RedisController::class, 'userMetadata']);
Route::post('/redis/user-notes', [RedisController::class, 'userNotes']);
Route::post('/redis/follows-metadata', [RedisController::class, 'followsMetadata']);

// Trending Events Controller
Route::get('/trending-events', [TrendingEventsController::class, 'index']);
Route::post('/trending-hashtags', [TrendingEventsController::class, 'show']);

