<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListenAuthorMetadata extends Command
{
    protected $signature = 'rabbitmq:author-metadata';
    protected $description = 'Listen for searched author metadata results';
}