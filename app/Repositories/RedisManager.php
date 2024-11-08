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

        $formatted_metadata = self::formatEventContent($decoded_metadata);
        return $formatted_metadata;
    }

    private static function formatEventContent($event)
    {
        if (!is_array($event)) {
            if (json_decode($event) !== null && json_last_error() === JSON_ERROR_NONE) {
                $event = json_decode($event, true);
            }
        }

        if (isset($event[2]["content"])) {
            $event[2]["content"] = json_decode($event[2]["content"], true);
        }
        return $event[2]["content"] ?? null;
    }

    public static function retrieveUserNotes(Request $request)
    {
        $user_pubkey = $request->input('publicKeyHex');
        $redis_key = "{$user_pubkey}:user-notes";
        $user_notes = Redis::sMembers($redis_key);
        return $user_notes;
    }

    private static function extractFollowsListPubkeys($event)
    {
        $follow_keys = array_column($event[2]['tags'], 1);
        foreach($follow_keys as $key => $value) {
            if (!ctype_xdigit($value) && !strlen($value)) {
                unset($follow_keys[$key]);
            }
        }
        return $follow_keys;
    }

    public static function retrieveFollowsMetadata(Request $request)
    {
        $user_pubkey = $request->input('publicKeyHex');

        $follows_metadata_redis_key = "follows_metadata";
        $follows_list_redis_key = "{$user_pubkey}:follows";

        $follows_metadata = Redis::sMembers($follows_metadata_redis_key);
        $follows_list = Redis::get($follows_list_redis_key);

        $decoded_follow_list_keys = self::extractFollowsListPubkeys($follows_list);

        if (!empty($decoded_follow_list_keys)) {
            dd($decoded_follow_list_keys);
        }
    }
}
