<?php

namespace App\Http\Controllers;

use App\Repositories\RabbitMQManager;
use Illuminate\Http\Request;

class RabbitMQController extends Controller
{
    public function getFollowsMetadata(Request $request) 
    {
        return RabbitMQManager::followMetadataQueue($request);
    }

    public function getSearchResults(Request $request)
    {
        return RabbitMQManager::searchResults($request);
    }
}
