<?php

namespace App\Http\Controllers;

use App\Repositories\RedisManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RedisController extends Controller
{
    public function userMetadata(Request $request)
    {
        $user_metadata = RedisManager::retrieveUsersMetadata($request);
        return ['userMetadata' => $user_metadata];
    }

    public function userNotes(Request $request)
    {
        $user_notes = RedisManager::retrieveUserNotes($request);
        return $user_notes;
    }

    public function followsMetadata(Request $request) {
         return RedisManager::retrieveFollowsMetadata($request);
    }

    public function followsNotes(Request $request) {
        return RedisManager::retrieveFollowsNotes($request);
    }
}
