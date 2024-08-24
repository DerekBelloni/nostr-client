<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test:command';
    protected $description = 'A test command';

    public function handle()
    {
        $this->info('Test command is working!');
    }
}