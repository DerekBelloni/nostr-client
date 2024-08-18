<?php

namespace App\Providers;

use App\Events\UserMetadataSet;
use App\Listeners\UserMetadata;
use App\Services\RabbitMQ\UserMetadataService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('user-metadata-service', function ($app) {
            return new UserMetadataService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
