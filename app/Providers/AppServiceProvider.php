<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Http\Client\Curl\Client as CurlClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // bind a PSR-18 client implementation so Elastic client can be resolved
        $this->app->bind(Psr18ClientInterface::class, function ($app) {
            // Http\Client\Curl\Client will discover PSR-17 factories (nyholm/psr7 installed)
            return new CurlClient();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
