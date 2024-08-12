<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static void listenForMetadata(string $publicKeyHex)
 * @method static mixed otherMethod(param $type)
 * // Add more method signatures here
 */
class UserMetadata extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'user-metadata-service';
    }
}