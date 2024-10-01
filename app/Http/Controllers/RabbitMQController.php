<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RabbitMQController extends Controller
{
    public function getFollowsMetadata(Request $request) 
    {
        dd($request->all());
    }
}
