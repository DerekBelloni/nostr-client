<?php

namespace App\Http\Controllers;

use App\Repositories\RedisManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RedisController extends Controller
{
    public function __construct(
        private RedisManager $redis_manager
    ){}
    
    public function followsList(Request $request) {
        return $this->redis_manager->checkFollowsList($request);
    }

    public function followsMetadata(Request $request) {
         return $this->redis_manager->retrieveFollowsMetadata($request);
    }

    public function followsNotes(Request $request) {
        return $this->redis_manager->retrieveFollowsNotes($request);
    }

    public function searchResults(Request $request) {
        return $this->redis_manager->retrieveSearchCache($request);
    }

    public function userMetadata(Request $request)
    {
        $user_metadata = $this->redis_manager->retrieveUsersMetadata($request);
        return ['userMetadata' => $user_metadata];
    }

    public function userNotes(Request $request)
    {
        $user_notes = $this->redis_manager->retrieveUserNotes($request);
        return $user_notes;
    }

    public function nostrEntities(Request $request) 
    {
        return $this->redis_manager->retrieveNostrEntities($request);
    }

    public function delete(Request $request) 
    {
        return $this->redis_manager->deleteSearchCache($request);
    }
}
