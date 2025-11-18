<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Http\Client\Curl\Client as CurlClient;
use Elastic\Transport\TransportBuilder;
use Elastic\Transport\NodePool\NodePoolInterface;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

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

        // Bind a default NodePool implementation so the container can instantiate Transport
        $this->app->singleton(NodePoolInterface::class, function () {
            return TransportBuilder::create()->getNodePool();
        });

        // Ensure a PSR logger exists for the Elasticsearch client
        $this->app->singleton(LoggerInterface::class, function () {
            return new NullLogger();
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
