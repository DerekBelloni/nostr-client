<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ListenFollowListMetadata extends Command
{
    protected $signature = 'rabbitmq:follows-metadata';
    protected $description = 'Listen for metadata of a users follows list';
}