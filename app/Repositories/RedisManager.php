<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisManager
{
    public static function retrieveUsersMetadata(Request $request)
    {
        $user_pubkey = $request->input('publicKeyHex');
        $decoded_metadata = json_decode(Redis::get("{$user_pubkey}:metadata"), true);
   
        abort_if(is_null($decoded_metadata), 404 ,"Metadata could not be found in Redis");
        
        $formatted_metadata = self::formatMetadata($decoded_metadata);
        return $formatted_metadata;
    }

    private static function formatMetadata($metadata)
    {
        if (isset($metadata[2]["content"])) {
            $metadata[2]["content"] = json_decode($metadata[2]["content"], true);
            Log::info("user metadata: ", [$metadata[2]["pubkey"]]);
        }
        return $metadata[2]["content"] ?? null;
    }

    public static function retrieveUserNotes(Request $request)
    {
        $user_pubkey = $request->input('publicKeyHex');
        $redis_key = "{$user_pubkey}:user-notes";
        $user_notes = Redis::sMembers($redis_key);
        return $user_notes;
    }
}