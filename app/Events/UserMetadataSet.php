<?php

namespace App\Events;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserMetadataSet 
{
    use Dispatchable, SerializesModels;

    public $pubHexKey;
    public $metadata;

    public function __construct($pubHexKey, $metadata)
    {
        $this->pubHexKey = $pubHexKey;
        $this->metadata = $metadata;
    }
}