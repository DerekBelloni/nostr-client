<?php

use App\Http\Controllers\NostrKeyController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RabbitMQController;
use App\Http\Controllers\RedisController;
use App\Http\Controllers\RelayMetadataController;
use App\Http\Controllers\SearchedEventsController;
use App\Http\Controllers\Trending\TrendingEventsController;
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
Route::post('/redis/follows-list', [RedisController::class, 'followsList']);
Route::post('/redis/follows-metadata', [RedisController::class, 'followsMetadata']);
Route::post('/redis/follows-notes', [RedisController::class, 'followsNotes']);
Route::post('/redis/search-results', [RedisController::class, 'searchResults']);
Route::post('/redis/user-metadata', [RedisController::class, 'userMetadata']);
Route::post('/redis/user-notes', [RedisController::class, 'userNotes']);
Route::post('/redis/clear-search-cachce', [RedisController::class, 'delete']);

// Relay Metadata Controller
Route::get('/relay-metadata', [RelayMetadataController::class, 'get']);

// Searched Events Controller
Route::post('/searched-events', [SearchedEventsController::class, 'index']);

// Trending Events Controller
Route::get('/trending-events', [TrendingEventsController::class, 'index']);
Route::post('/trending-hashtags', [TrendingEventsController::class, 'show']);

